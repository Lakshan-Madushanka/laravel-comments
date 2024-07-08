<?php

namespace LakM\Comments\Livewire;

use GrahamCampbell\Security\Facades\Security;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use LakM\Comments\Actions\CreateCommentAction;
use LakM\Comments\Contracts\CommentableContract;
use LakM\Comments\Data\UserData;
use LakM\Comments\Helpers;
use LakM\Comments\Repository;
use LakM\Comments\ValidationRules;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;
use Spatie\Honeypot\Http\Livewire\Concerns\HoneypotData;
use Spatie\Honeypot\Http\Livewire\Concerns\UsesSpamProtection;

class CreateCommentForm extends Component
{
    use UsesSpamProtection;

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

    public string $guest_name = '';

    public string $guest_email = '';

    public string $text = "";

    public string $editorId;

    #[Locked]
    public bool $authenticated;

    #[Locked]
    public bool $guestModeEnabled;

    public bool $disableEditor = false;

    /**
     * @param  Model&CommentableContract  $model
     * @return void
     */
    public function mount(Model $model): void
    {
        Helpers::checkCommentableModelValidity($model);

        $this->editorId =  Str::uuid();

        $this->model = $model;

        $this->authenticated = $this->model->authCheck();
        $this->guestModeEnabled = $this->model->guestModeEnabled();

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
            CreateCommentAction::execute($this->model, $this->getFormData(), $this->guest);

            $this->clear();

            $this->dispatch('comment-created', id: $this->editorId, approvalRequired: $this->approvalRequired);

            $this->dispatch('reset-editor-' . $this->editorId, value: $this->text);

            $this->setLimitExceededStatus();
        }
    }

    private function getFormData(): array
    {
        $data = $this->only('guest_name', 'guest_email', 'text');
        return $this->clearFormData($data);
    }

    private function clearFormData(array $data): array
    {
        return array_map(function (string $value) {
            return Security::clean($value);
        }, $data);
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
            $this->guest = Repository::guest();

            $this->guest_name = $this->guest->name;
            $this->guest_email = $this->guest->email;
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

    public function render(): View|Factory|Application
    {
        return view('comments::livewire.create-comment-form');
    }
}
