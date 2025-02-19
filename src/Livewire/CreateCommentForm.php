<?php

namespace LakM\Comments\Livewire;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use LakM\Comments\Abstracts\AbstractQueries;
use LakM\Comments\Actions\CreateCommentAction;
use LakM\Comments\Contracts\CommentableContract;
use LakM\Comments\Data\GuestData;
use LakM\Comments\Data\MessageData;
use LakM\Comments\Data\UserData;
use LakM\Comments\Helpers;
use LakM\Comments\SecureGuestModeManager;
use LakM\Comments\ValidationRules;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;
use Mews\Purifier\Facades\Purifier;
use Spatie\Honeypot\Http\Livewire\Concerns\HoneypotData;
use Spatie\Honeypot\Http\Livewire\Concerns\UsesSpamProtection;

class CreateCommentForm extends Component
{
    use UsesSpamProtection;

    public SecureGuestModeManager $secureGuestMode;

    /** @var Model&CommentableContract */
    #[Locked]
    public Model $model;

    #[Locked]
    public bool $loginRequired;

    #[Locked]
    public bool $limitExceeded;

    #[Locked]
    public bool $approvalRequired;

    public HoneypotData $honeyPostData;

    public ?UserData $guest = null;

    public string $name = '';
    public string $email = '';

    public string $text = "";

    public string $editorId;

    #[Locked]
    public bool $authenticated;

    #[Locked]
    public bool $guestModeEnabled;

    public bool $disableEditor = false;

    public bool $rateLimitExceeded = false;

    public bool $verifyLinkSent = false;

    public bool $guestEmailVerified = false;

    /**
     * @param  Model&CommentableContract  $model
     * @return void
     * @throws \Throwable
     */
    public function mount(Model $model): void
    {
        Helpers::checkCommentableModelValidity($model);

        $this->editorId =  Str::uuid();

        $this->model = $model;

        $this->secureGuestMode = app(SecureGuestModeManager::class);

        $this->authenticated = $this->model->authCheck();
        $this->guestModeEnabled = $this->model->guestModeEnabled();

        $this->disableEditor = $this->guestModeEnabled && !$this->secureGuestMode->allowed();

        $this->showGuestEmailVerifiedMessage();

        $this->setLoginRequired();

        $this->setLimitExceededStatus();

        $this->setGuest();

        $this->setApprovalRequired();

        $this->honeyPostData = new HoneypotData();
    }

    public function rules(): array
    {
        return ValidationRules::get($this->model, 'create');
    }

    /**
     * @throws \Exception
     */
    public function create(): void
    {
        $this->protectAgainstSpam();

        $this->validate();

        if ($this->model->canCreateComment(Auth::guard($this->model->getAuthGuard())->user())) {
            CreateCommentAction::execute(
                $this->model,
                MessageData::fromArray($this->getFormData()),
                $this->getGuestData()
            );

            $this->clear();

            $this->dispatch('comment-created', id: $this->editorId, approvalRequired: $this->approvalRequired);

            $this->dispatch('reset-editor-' . $this->editorId, value: $this->text);

            $this->setLimitExceededStatus();
        }
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

    public function showGuestEmailVerifiedMessage(): void
    {
        if (session()->has('guest-email-verified')) {
            $this->guestEmailVerified = true;
        }
    }

    public function setLoginRequired(): void
    {
        $this->loginRequired = !$this->authenticated && !$this->guestModeEnabled;

        if ($this->loginRequired) {
            $this->disableEditor = true;
        }
    }

    public function setLimitExceededStatus(): void
    {
        $this->limitExceeded = $this->model->limitExceeded($this->model->getAuthUser());

        if ($this->limitExceeded) {
            $this->disableEditor = true;
            $this->dispatch('disable-editor-' . $this->editorId);
        }
    }

    #[On('guest-credentials-changed')]
    public function setGuest(): void
    {
        if ($this->guestModeEnabled) {
            $this->guest = app(AbstractQueries::class)->guest();

            $this->name = $this->guest->name;
            $this->email = $this->guest->email;
        }
    }

    public function setApprovalRequired(): void
    {
        $this->approvalRequired = $this->model->approvalRequired();
    }

    public function clear(): void
    {
        $this->resetValidation();
        $this->reset('text');
    }

    public function redirectToLogin(string $redirectUrl): void
    {
        session(['url.intended' => $redirectUrl]);
        $this->redirect(config('comments.login_route'));
    }

    public function sendVerifyLink(string $url): void
    {
        Validator::make(
            ['name' => $this->name, 'email' => $this->email],
            [
                'name' => ['required', Rule::unique('guests')->whereNot('email', $this->email)],
                'email' => ['required', 'email'],
            ]
        )->validate();

        if (!$this->secureGuestMode->limitLinkSending($this->email)) {
            $this->rateLimitExceeded = true;
        }

        $this->secureGuestMode->sendLink($this->name, $this->email, $url);

        $this->verifyLinkSent = true;
    }

    public function render(): View|Factory|Application
    {
        return view('comments::livewire.create-comment-form');
    }
}
