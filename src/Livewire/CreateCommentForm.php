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
use LakM\Comments\ValidationRules;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Spatie\Honeypot\Http\Livewire\Concerns\HoneypotData;
use Spatie\Honeypot\Http\Livewire\Concerns\UsesSpamProtection;

class CreateCommentForm extends Component
{
    use UsesSpamProtection;

    #[Locked]
    public Model $model;

    #[Locked]
    public bool $loginRequired;

    #[Locked]
    public bool $limitExceeded;

    public HoneypotData $honeyPostData;

    public string $guest_name = '';

    public string $guest_email = '';

    public string $text = "";

    public string $editorId;
    public string $toolbarId;

    #[Locked]
    public bool $authenticated;

    #[Locked]
    public bool $guestModeEnabled;

    /**
     * @param  string  $modelClass
     * @param  mixed  $modelId
     * @return void
     */
    public function mount(Model $model): void
    {
        $this->model = $model;

        $this->authenticated = $this->model->authCheck();
        $this->guestModeEnabled = $this->model->guestModeEnabled();

        $this->setLoginRequired();

        $this->limitExceeded = $this->model->limitExceeded($this->model, $model->getAuthUser());

        $this->honeyPostData = new HoneypotData();

        $this->editorId = 'editor'.Str::random();
        $this->toolbarId = 'toolbar'.Str::random();
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

        if ($this->model->canCreateComment($this->model, Auth::user())) {
            CreateCommentAction::execute($this->model, $this->getFormData());

            $this->clear();
            $this->dispatch('comment-created', id: $this->editorId);
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
    }

    public function clear(): void
    {
        $this->resetValidation();
        $this->reset('guest_name', 'guest_email', 'text');
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
