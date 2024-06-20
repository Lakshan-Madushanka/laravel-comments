<?php

namespace LakM\Comments\Livewire;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use LakM\Comments\Actions\DeleteCommentReplyAction;
use LakM\Comments\Models\Comment;
use LakM\Comments\Models\Reply;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

class CommentReplyItem extends Component
{

    #[Locked]
    public Model $relatedModel;

    #[Locked]
    public Comment $comment;

    #[Locked]
    public Reply $reply;

    #[Locked]
    public bool $guestMode;

    #[Locked]
    public bool $authMode;

    public bool $show = false;

    public function mount(
        Comment $comment,
        Reply $reply,
        Model $relatedModel,
        bool $guestMode,
    ): void {
        $this->comment = $comment;
        $this->reply = $reply;
        $this->guestMode = $guestMode;
        $this->authMode = !$guestMode;

        $this->guestMode = $guestMode;

        $this->authMode = !$guestMode;

        $this->relatedModel = $relatedModel;

        $this->setProfileUrl();
    }

    public function canUpdateReply(Reply $reply): bool
    {
        return Gate::allows('update-reply', [$reply, $this->guestMode]);
    }

    public function canDeleteReply(Reply $reply): bool
    {
        return Gate::allows('delete-reply', [$reply, $this->guestMode]);
    }

    public function delete(Reply $reply): void
    {
        if ($this->canDeleteReply($reply) && DeleteCommentReplyAction::execute($reply)) {
            $this->dispatch('reply-deleted', replyId: $reply->getKey(), commentId: $this->comment->getKey());
        }
    }

    private function setProfileUrl(): void
    {
        if($user = $this->relatedModel->getAuthUser()) {
            $this->profileUrl = $user->profileUrl();
        }
    }

    #[On('show-replies.{comment.id}')]
    public function setShowStatus(): void
    {
        $this->show = !$this->show;
    }

    public function render(): View|Factory|Application
    {
        return view('comments::livewire.comment-reply-item');
    }
}
