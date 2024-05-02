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
use LakM\Comments\Repository;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class CommentReplyList extends Component
{
    use WithPagination;

    public bool $show = false;

    #[Locked]
    public Comment $comment;

    #[Locked]
    public Model $relatedModel;

    public int $total;

    public int $limit = 15;

    public int $perPage;

    #[Locked]
    public bool $guestMode;

    #[Locked]
    public bool $authMode;

    #[Locked]
    public bool $approvalRequired;

    public function mount(Comment $comment, Model $relatedModel, int $total): void
    {
        if (!$this->show) {
            $this->skipRender();
        }

        $this->comment = $comment;
        $this->relatedModel = $relatedModel;

        $this->total = $total;

        $this->perPage = config('comments.reply.pagination.per_page');
        $this->limit = config('comments.reply.pagination.per_page');

        $this->guestMode = $this->relatedModel->guestModeEnabled();

        $this->authMode = !$this->relatedModel->guestModeEnabled();

        $this->setApprovalRequired();
    }

    public function paginate()
    {
        $this->limit += $this->perPage;
    }

    public function delete(Reply $reply): void
    {
        if($this->canDeleteReply($reply) && DeleteCommentReplyAction::execute($reply)) {
            $this->dispatch('reply-deleted', replyId: $reply->getKey(), commentId: $this->comment->getKey());

            $this->total -= 1;
        }
    }

    #[On('reply-created')]
    public function onReplyCreated($commentId)
    {
        if ($commentId === $this->comment->getKey()) {
            $this->total += 1;
        }
    }

    public function canUpdateReply(Reply $reply): bool
    {
        return Gate::allows('update-reply', $reply);
    }

    public function canDeleteReply(Reply $reply): bool
    {
        return Gate::allows('delete-reply', $reply);
    }

    public function setApprovalRequired()
    {
        $this->approvalRequired = config('comments.reply.approval_required');
    }

    #[On('show-replies.{comment.id}')]
    public function setShowStatus()
    {
        $this->show = !$this->show;
    }


    public function render(): View|Factory|Application
    {
        return view('comments::livewire.comment-replies-list',
            ['replies' => Repository::commentReplies($this->comment, $this->relatedModel, $this->approvalRequired, $this->limit)]);
    }
}
