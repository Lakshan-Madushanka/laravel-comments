<?php

namespace LakM\Comments\Livewire;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use LakM\Comments\Actions\DeleteCommentReplyAction;
use LakM\Comments\Contracts\CommentableContract;
use LakM\Comments\Models\Comment;
use LakM\Comments\Models\Reply;
use Livewire\Attributes\Locked;
use Livewire\Component;

class CommentReplyItem extends Component
{
    /** @var Model&CommentableContract */
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

    #[Locked]
    public bool $canManipulate;

    public ?string $profileUrl;

    public bool $shouldEnableShareButton = false;

    /**
     * @param  Comment $comment
     * @param  Reply $reply
     * @param  Model&CommentableContract $relatedModel
     * @param  bool $guestMode
     * @return void
     */
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

        $this->relatedModel = $relatedModel;

        $this->setProfileUrl();
        $this->setCanManipulate();
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
        $this->skipRender();

        if ($this->canDeleteReply($reply) && DeleteCommentReplyAction::execute($reply)) {
            $this->dispatch('reply-deleted-' . $this->comment->getKey(), replyId: $reply->getKey(), commentId: $this->comment->getKey());
        }
    }

    private function setProfileUrl(): void
    {
        $this->profileUrl = $this->comment->commenter->profileUrl();
    }

    public function setCanManipulate(): bool
    {
        return $this->canManipulate = $this->canUpdateReply($this->reply) || $this->canDeleteReply($this->reply);
    }


    public function render(): View|Factory|Application
    {
        return view('comments::livewire.comment-reply-item');
    }
}
