<?php

namespace LakM\Comments\Livewire;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use LakM\Comments\Actions\DeleteCommentAction;
use LakM\Comments\Models\Comment;
use LakM\Comments\Repository;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class CommentReplyList extends Component
{
    use WithPagination;

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

    public function mount(Comment $comment, Model $relatedModel): void
    {
        $this->comment = $comment;
        $this->relatedModel = $relatedModel;

        $this->total = Repository::getCommentReplyCount($this->comment);

        $this->perPage = config('comments.reply.pagination.per_page');
        $this->limit = config('comments.reply.pagination.per_page');

        $this->guestMode = $this->relatedModel->guestModeEnabled();

        $this->authMode = !$this->relatedModel->guestModeEnabled();

    }

    public function paginate()
    {
        $this->limit += $this->perPage;
    }

//    public function delete(Comment $comment, DeleteCommentAction $deleteCommentAction): void
//    {
//        if($this->model->canDeleteComment($comment) && $deleteCommentAction->execute($comment)) {
//            $this->dispatch('comment-deleted', commentId: $comment->getKey());
//
//            $this->total -= 1;
//        }
//    }

    #[On('reply-created')]
    public function onReplyCreated($commentId)
    {
        if ($commentId === $this->comment->getKey()) {
            $this->total += 1;
            $this->render();
        }
    }

    public function render(): View|Factory|Application
    {
        return view('comments::livewire.comment-replies-list',
            ['replies' => Repository::commentReplies($this->comment, $this->relatedModel, $this->limit)]);
    }
}
