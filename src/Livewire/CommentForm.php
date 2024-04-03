<?php

namespace LakM\Comments\Livewire;

use Illuminate\Database\Eloquent\Model;
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
    }

    public function rules()
    {
        return ValidationRules::get($this->model);
    }

    public function create()
    {
        $this->validate();

        if ($this->model->canCreateComment()) {
            CreateCommentAction::execute($this->model, $this->only('guest_name', 'guest_email', 'text'));
        }

       $this->clear();
    }

    public function setLoginRequired(): void
    {
        $this->loginRequired = !$this->authenticated && !$this->guestModeEnabled;
    }

    public function clear()
    {
        $this->resetValidation();
        $this->reset('guest_name', 'guest_email', 'text');
    }

    public function render()
    {
        return view('comments::livewire.comment-form');
    }
}
