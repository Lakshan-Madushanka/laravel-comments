<?php

namespace LakM\Comments\Livewire;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use LakM\Comments\Actions\DeleteCommentAction;
use LakM\Comments\Models\Comment;
use Livewire\Attributes\Locked;
use Livewire\Component;

class CommentItem extends Component
{
    #[Locked]
    public Model $model;

    #[Locked]
    public Comment $comment;

    #[Locked]
    public bool $guestMode;

    #[Locked]
    public bool $authMode;

    #[Locked]
    public bool $showReplyList;

    public function mount(
        Comment $comment,
        bool $guestMode,
        Model $model,
        bool $showReplyList,
    ): void {
        $this->comment = $comment;
        $this->guestMode = $guestMode;
        $this->model = $model;
        $this->showReplyList = $showReplyList;
        $this->authMode = !$guestMode;

        $this->setProfileUrl();
    }

    public function delete(Comment $comment): void
    {
        if ($this->model->canDeleteComment($comment) && DeleteCommentAction::execute($comment)) {
            $this->dispatch('comment-deleted', commentId: $comment->getKey());
        }
    }

    private function setProfileUrl(): void
    {
        if ($user = $this->model->getAuthUser()) {
            $this->profileUrl = $user->profileUrl();
        }
    }

    public function render(): View|Factory|Application
    {
        return view('comments::livewire.comment-item');
    }
}
