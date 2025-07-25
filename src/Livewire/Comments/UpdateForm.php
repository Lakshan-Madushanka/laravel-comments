<?php

namespace LakM\Commenter\Livewire\Comments;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use LakM\Commenter\Actions\Comment\UpdateAction;
use LakM\Commenter\Contracts\CommentableContract;
use LakM\Commenter\Helpers;
use LakM\Commenter\Models\Comment;
use LakM\Commenter\ValidationRules;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Mews\Purifier\Facades\Purifier;

class UpdateForm extends Component
{
    public string $editorId;

    public string $text;

    #[Locked]
    public Comment $comment;

    /** @var Model&CommentableContract */
    #[Locked]
    public Model $model;

    #[Locked]
    public bool $approvalRequired;

    /**
     * @param  Comment  $comment
     * @param  Model&CommentableContract  $model
     * @return void
     * @throws \Throwable
     */
    public function mount(Comment $comment, Model $model): void
    {
        Helpers::checkCommentableModelValidity($model);

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

        if ($this->model->canEditComment($this->comment) && UpdateAction::execute($this->comment, $data)) {
            $this->dispatch('comment-updated', commentId: $this->comment->getKey(), text: $data['text']);

            $this->resetValidation();
        }
    }

    private function getFormData(): array
    {
        return Purifier::clean($this->only('text'));
    }

    public function discard(): void
    {
        $this->text = $this->comment->text;
        $this->dispatch('comment-update-discarded', commentId: $this->comment->getKey());
        $this->dispatch('reset-editor-' . $this->editorId, value: $this->comment->text);
    }

    public function render()
    {
        return view('commenter::livewire.comments.update-form');
    }
}
