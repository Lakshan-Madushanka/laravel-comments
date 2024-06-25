<?php

namespace LakM\Comments\Livewire;

use GrahamCampbell\Security\Facades\Security;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use LakM\Comments\Actions\UpdateCommentAction;
use LakM\Comments\Models\Comment;
use LakM\Comments\ValidationRules;
use Livewire\Attributes\Locked;
use Livewire\Component;

class UpdateCommentForm extends Component
{
    public string $editorId;

    public string $text;

    #[Locked]
    public Comment $comment;

    #[Locked]
    public Model $model;

    #[Locked]
    public bool $approvalRequired;

    public function mount(Comment $comment, Model $model): void
    {
        $this->editorId =  Str::uuid();

        $this->comment = $comment;
        $this->text = $this->comment->text;

        $this->model = $model;

        $this->approvalRequired = $model->approvalRequired();
    }

    public function rules(): array
    {
        return ValidationRules::get($this->comment, 'update');
    }

    public function save(): void
    {
        $this->validate();

        $data = $this->getFormData();

        if ($this->model->canEditComment($this->comment) && UpdateCommentAction::execute($this->comment, $data)) {
            $this->dispatch('comment-updated', commentId: $this->comment->getKey(), text: $data['text']);

            $this->resetValidation();
        }
    }

    private function getFormData(): array
    {
        return Security::clean($this->only('text'));
    }

    public function discard(): void
    {
        $this->text = $this->comment->text;
        $this->dispatch('comment-update-discarded', commentId: $this->comment->getKey());
        $this->dispatch('reset-editor-' . $this->editorId, value: $this->comment->text);
    }

    public function render()
    {
        return view('comments::livewire.update-comment-form');
    }
}
