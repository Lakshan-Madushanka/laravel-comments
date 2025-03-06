<?php

namespace LakM\Comments\Livewire;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use LakM\Comments\Actions\DeleteCommentAction;
use LakM\Comments\Contracts\CommentableContract;
use LakM\Comments\Helpers;
use LakM\Comments\Models\Comment;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Throwable;

class CommentItem extends Component
{
    /** @var Model&CommentableContract */
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

    #[Locked]
    public bool $canManipulate;

    public ?string $profileUrl = null;

    public bool $shouldEnableShareButton = true;

    /**
     * @param  Comment $comment
     * @param  bool $guestMode
     * @param  Model&CommentableContract $model
     * @param  bool $showReplyList
     * @return void
     * @throws Throwable
     */
    public function mount(
        Comment $comment,
        bool $guestMode,
        Model $model,
        bool $showReplyList,
    ): void {
        Helpers::checkCommentableModelValidity($model);

        $this->comment = $comment;
        $this->guestMode = $guestMode;
        $this->model = $model;
        $this->showReplyList = $showReplyList;
        $this->authMode = !$guestMode;

        $this->setProfileUrl();
        $this->setCanManipulate();
    }

    public function delete(Comment $comment): void
    {
        $this->skipRender();

        if ($this->model->canDeleteComment($comment) && DeleteCommentAction::execute($comment)) {
            $this->dispatch('comment-deleted', commentId: $comment->getKey());
        }
    }

    private function setProfileUrl(): void
    {
        $this->profileUrl = $this->comment->commenter->profileUrl();
    }

    public function setCanManipulate(): bool
    {
        return $this->canManipulate = $this->model->canEditComment($this->comment) || $this->model->canDeleteComment($this->comment);
    }

    public function render(): View|Factory|Application
    {
        return view('comments::livewire.comment-item');
    }
}
