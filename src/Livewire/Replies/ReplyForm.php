<?php

namespace LakM\Commenter\Livewire\Replies;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use LakM\Commenter\Abstracts\AbstractQueries;
use LakM\Commenter\Actions\Reply\CreateAction;
use LakM\Commenter\Contracts\CommentableContract;
use LakM\Commenter\Data\GuestData;
use LakM\Commenter\Data\MessageData;
use LakM\Commenter\Data\UserData;
use LakM\Commenter\Exceptions\ReplyLimitExceededException;
use LakM\Commenter\Models\Message;
use LakM\Commenter\SecureGuestModeManager;
use LakM\Commenter\ValidationRules;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;
use Mews\Purifier\Facades\Purifier;
use Spatie\Honeypot\Http\Livewire\Concerns\HoneypotData;
use Spatie\Honeypot\Http\Livewire\Concerns\UsesSpamProtection;

class ReplyForm extends Component
{
    use UsesSpamProtection;

    public bool $show = false;

    public SecureGuestModeManager $secureGuestMode;

    #[Locked]
    public Message $message;

    /** @var Model&CommentableContract  */
    #[Locked]
    public Model $relatedModel;

    private AbstractQueries $queries;

    #[Locked]
    public bool $loginRequired;

    #[Locked]
    public bool $limitExceeded;

    #[Locked]
    public bool $approvalRequired;

    #[Locked]
    public bool $disableEditor;

    public HoneypotData $honeyPostData;

    public ?UserData $guest = null;

    public ?string $name = '';

    public ?string $email = '';

    public string $text = "";

    public string $editorId;

    #[Locked]
    public bool $authenticated;

    #[Locked]
    public bool $guestMode;

    /**
     * Current user reply count
     * @var int
     */
    public int $replyCount;

    public function boot(): void
    {
        $this->queries = app(AbstractQueries::class);
    }

    /**
     * @param  Message  $message
     * @param  Model&CommentableContract  $relatedModel
     * @param  bool  $guestMode
     * @return void
     */
    public function mount(Message $message, Model $relatedModel, bool $guestMode): void
    {
        if (!$this->show) {
            $this->skipRender();
        }

        $this->secureGuestMode = app(SecureGuestModeManager::class);

        $this->message = $message;
        $this->relatedModel = $relatedModel;

        $this->guestMode = $guestMode;

        $this->disableEditor = $this->guestMode && !$this->secureGuestMode->allowed();

        $this->authenticated = $this->relatedModel->authCheck();

        $this->editorId = Str::uuid();

        $this->setLoginRequired();

        $this->setApprovalRequired();

        $this->honeyPostData = new HoneypotData();
    }

    public function rules(): array
    {
        return ValidationRules::get($this->relatedModel, 'create');
    }

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    public function create(): void
    {
        $this->protectAgainstSpam();

        $this->validate();

        if (!$this->guestMode) {
            Gate::authorize('create-reply');
        }

        throw_if($this->limitExceeded, ReplyLimitExceededException::make($this->replyLimit()));

        CreateAction::execute(
            $this->message,
            MessageData::fromArray($this->getFormData()),
            $this->guestMode,
            $this->getGuestData(),
        );

        $this->dispatch(
            'reply-created-' . $this->message->id,
            editorId: $this->editorId,
            messageId: $this->message->getKey(),
            approvalRequired: $this->approvalRequired
        );

        $this->dispatch('reset-editor-' . $this->editorId, value: "");

        if (!$this->guest->name ||
            ($this->guest->name !== $this->name || $this->guest->email !== $this->email)) {
            $this->dispatch('guest-credentials-changed');
        }

        $this->incrementReplyCount();

        $this->setLimitExceeded();

        $this->clear();

        $this->setGuest();
    }

    private function getGuestData(): GuestData
    {
        if ($this->secureGuestMode->enabled()) {
            return new GuestData(
                $this->secureGuestMode->user()->name,
                $this->secureGuestMode->user()->email,
            );
        }

        return GuestData::fromArray($this->only('name', 'email'));
    }

    private function getFormData(): array
    {
        $data = $this->only('name', 'email', 'text');
        return $this->clearFormData($data);
    }

    private function clearFormData(array $data): array
    {
        return array_map(function (string $value) {
            return Purifier::clean($value);
        }, $data);
    }

    public function discard(): void
    {
        $this->dispatch('reply-discarded', messageId: $this->message->getKey());
        $this->dispatch('reset-editor-' . $this->editorId, value: '');
    }

    public function setLoginRequired(): void
    {
        $this->loginRequired = !$this->authenticated && !$this->guestMode;

        if ($this->loginRequired) {
            $this->dispatch('disable-editor-' . $this->editorId);
        }
    }

    public function incrementReplyCount(): void
    {
        $this->replyCount += 1;
    }

    public function setLimitExceeded(): void
    {
        $limit = $this->replyLimit();


        if (is_null($limit)) {
            $this->limitExceeded = false;
            return;
        }

        $this->limitExceeded = $this->replyCount >= $limit;

        if ($this->limitExceeded) {
            $this->dispatch('disable-editor-' . $this->editorId);
        }
    }

    public function setApprovalRequired(): void
    {
        $this->approvalRequired = config('commenter.reply.approval_required');
    }

    public function replyLimit(): ?int
    {
        return config('commenter.reply.limit');
    }

    private function setGuest(): void
    {
        if ($this->guestMode) {
            $this->guest = $this->queries->guest();

            $this->name = $this->guest->name;
            $this->email = $this->guest->email;

            return;
        }

        $this->guest = new UserData(null, null);
    }

    public function setReplyCount(): void
    {
        $this->replyCount = $this->queries->userReplyCountForMessage(
            $this->message,
            $this->guestMode,
            $this->relatedModel->getAuthUser()
        );
    }

    public function clear(): void
    {
        $this->resetValidation();
        $this->reset('text');
    }

    public function redirectToLogin(string $redirectUrl): void
    {
        session(['url.intended' => $redirectUrl]);
        $this->redirect(config('commenter.login_route'));
    }

    #[On('show-create-reply-form-{message.id}')]
    public function showForm(): void
    {
        if (!$this->show) {
            $this->show = true;
        }

        if (!isset($this->replyCount)) {
            $this->setReplyCount();
        }

        if ($this->show && !isset($this->limitExceeded)) {
            $this->setLimitExceeded();
        }

        $this->setGuest();
    }

    public function render(): View|Factory|Application
    {
        return view('commenter::livewire.replies.create-form');
    }
}
