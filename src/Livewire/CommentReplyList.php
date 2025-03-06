<?php

namespace LakM\Comments\Livewire;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use LakM\Comments\Abstracts\AbstractQueries;
use LakM\Comments\Contracts\CommentableContract;
use LakM\Comments\Enums\Sort;
use LakM\Comments\Livewire\Concerns\HasSingleThread;
use LakM\Comments\Models\Comment;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * @property \Illuminate\Support\Collection|LengthAwarePaginator $replies
 */
class CommentReplyList extends Component
{
    use WithPagination;
    use HasSingleThread;

    public bool $show = false;

    #[Locked]
    public Comment $comment;

    /** @var Model&CommentableContract */
    #[Locked]
    public Model $relatedModel;

    public int $total;

    public int $currentTotal;

    public int $limit = 15;

    public int $perPage;

    public bool $paginationRequired;

    #[Locked]
    public bool $guestMode;

    #[Locked]
    public bool $approvalRequired;

    public Sort $sortBy;

    public string $filter = '';

    public bool $showFilters = true;

    /**
     * @param  Comment  $comment
     * @param  Model&CommentableContract  $relatedModel
     * @param  int  $total
     * @return void
     */
    public function mount(Comment $comment, Model $relatedModel, int $total): void
    {
        if (!$this->show) {
            $this->skipRender();
        }

        $this->comment = $comment;
        $this->relatedModel = $relatedModel;

        $this->total = $total;
        $this->currentTotal = $total;

        $this->perPage = config('comments.reply.pagination.per_page');
        $this->limit = config('comments.reply.pagination.per_page');

        $this->sortBy = $relatedModel->getRepliesSortOrder();

        $this->guestMode = !$this->relatedModel->guestModeEnabled();

        $this->setPaginationRequired();

        $this->setApprovalRequired();

        $this->showFilters = !$this->shouldShowSingleThread();
    }

    public function paginate(): void
    {
        $this->limit += $this->perPage;

        $this->dispatch('more-replies-loaded');
    }

    public function setSortBy(Sort $sortBy): void
    {
        $this->sortBy = $sortBy;

        $this->dispatchFilterAppliedEvent();
    }

    public function setFilter(string $filter): void
    {
        if ($this->filter) {
            $this->filter = '';
        } else {
            $this->filter = $filter;
        }

        $this->setTotalRepliesCount();
        $this->setPaginationRequired();
        $this->dispatchFilterAppliedEvent();
    }

    public function setTotalRepliesCount(): void
    {
        $this->currentTotal = $this->replies->count();

        if ($this->replies instanceof LengthAwarePaginator) {
            $this->currentTotal = $this->replies->total();
        }
    }

    #[On('show-replies.{comment.id}')]
    public function setShowStatus(): void
    {
        $this->show = !$this->show;

        $this->dispatch('show-reply');
    }

    #[On('reply-created-{comment.id}')]
    public function onReplyCreated($commentId): void
    {
        if ($this->approvalRequired) {
            return;
        }

        if ($commentId === $this->comment->getKey()) {
            $this->total += 1;
        }
    }

    #[On('reply-deleted-{comment.id}')]
    public function onReplyDeleted($commentId): void
    {
        if ($commentId === $this->comment->getKey()) {
            $this->total -= 1;
        }
    }

    private function setPaginationRequired(): void
    {
        $this->paginationRequired = $this->limit < $this->total;
    }

    public function setApprovalRequired(): void
    {
        $this->approvalRequired = config('comments.reply.approval_required');
    }

    public function dispatchFilterAppliedEvent(): void
    {
        $this->dispatch('filter-applied');
    }

    #[Computed]
    public function replies(): Collection|LengthAwarePaginator
    {
        return app(AbstractQueries::class)
            ->commentReplies(
                $this->comment,
                $this->relatedModel,
                $this->approvalRequired,
                $this->limit,
                $this->sortBy,
                $this->filter
            );
    }

    public function render(): View|Factory|Application
    {
        return view(
            'comments::livewire.comment-reply-list',
            ['replies' => $this->replies]
        );
    }
}
