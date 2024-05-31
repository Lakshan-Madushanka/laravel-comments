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

class CommentList extends Component
{
    use WithPagination;

    #[Locked]
    public Model $model;

    public int $total;

    public int $limit = 15;

    public int $perPage;

    #[Locked]
    public bool $guestMode;

    #[Locked]
    public bool $authMode;

    public string $sortBy = 'top';

    public string $filter = '';

    public bool $showReplyList = false;

    public function mount(Model $model): void
    {
        $this->model = $model;

        $this->total = Repository::getTotalCommentsCountForRelated($this->model);

        $this->perPage = config('comments.pagination.per_page');
        $this->limit = config('comments.pagination.per_page');

        $this->guestMode = $this->model->guestModeEnabled();

        $this->authMode = !$this->model->guestModeEnabled();
    }

    public function paginate()
    {
        $this->limit += $this->perPage;

        $this->dispatch('more-comments-loaded');
    }

    public function delete(Comment $comment): void
    {
        if ($this->model->canDeleteComment($comment) && DeleteCommentAction::execute($comment)) {
            $this->dispatch('comment-deleted', commentId: $comment->getKey());
            $this->total -= 1;
        }
    }

    public function setSortBy(string $sortBy): void
    {
        $this->sortBy = $sortBy;
    }

    #[On('comment-created')]
    public function increaseCommentCount(bool $approvalRequired): void
    {
        if ($approvalRequired) {
            return;
        }

        $this->total += 1;

        $this->showReplyList = true;
    }

    public function render(): View|Factory|Application
    {
        return view(
            'comments::livewire.comment-list',
            ['comments' => Repository::allRelatedComments($this->model, $this->limit, $this->sortBy, $this->filter)]
        );
    }
}
