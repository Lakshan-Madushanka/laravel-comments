<?php

namespace LakM\Comments\Livewire;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use LakM\Comments\Actions\CreateCommentAction;
use LakM\Comments\ValidationRules;
use Livewire\Attributes\Locked;
use Livewire\Component;

class CommentForm extends Component
{
    #[Locked]
    public Model $model;

    #[Locked]
    public bool $loginRequired;

    #[Locked]
    public bool $limitExceeded;

    public string $guest_name = '';

    public string $guest_email = '';

    public string $text = '';

    /**
     * @param  string  $modelClass
     * @param  mixed  $modelId
     * @return void
     */
    public function mount(string $modelClass, mixed $modelId): void
    {
        $this->model = $modelClass::findOrFail($modelId);

        $this->authenticated = $this->model->authCheck();
        $this->guestModeEnabled = $this->model->guestModeEnabled();

        $this->setLoginRequired();

        $this->limitExceeded = $this->model->limitExceeded($this->model, Auth::user());
    }

    public function rules(): array
    {
        return ValidationRules::get($this->model);
    }

    public function create(): void
    {
        if ($this->model->canCreateComment($this->model, Auth::user())) {
            CreateCommentAction::execute($this->model, $this->only('guest_name', 'guest_email', 'text'));

            $this->clear();

            $this->dispatch('comment-created');
        }
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
        return view('comments::livewire.comment-form');
    }
}
