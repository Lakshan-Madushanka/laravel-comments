<?php

namespace LakM\Comments\Livewire;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use LakM\Comments\Repository;
use Livewire\Attributes\Computed;
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

    public bool $paginationRequired;

    public string $sortBy = 'top';

    public string $filter = '';

    public bool $showReplyList = false;

    public function mount(Model $model): void
    {
        $this->model = $model;

        $this->setTotalCommentsCount();

        $this->perPage = config('comments.pagination.per_page');
        $this->limit = config('comments.pagination.per_page');

        $this->guestMode = $this->model->guestModeEnabled();

        $this->setPaginationRequired();
    }

    public function paginate(): void
    {
        $this->limit += $this->perPage;

        $this->dispatch('more-comments-loaded');
    }

    public function setTotalCommentsCount(): void
    {
        $this->total = $this->comments->total();
    }

    public function setSortBy(string $sortBy): void
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

        $this->setTotalCommentsCount();
        $this->setPaginationRequired();
        $this->dispatchFilterAppliedEvent();
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

    #[On('comment-deleted')]
    public function onReplyDeleted($commentId): void
    {
        $this->total -= 1;
    }

    private function setPaginationRequired(): void
    {
        $this->paginationRequired = $this->limit < $this->total;
    }

    public function dispatchFilterAppliedEvent(): void
    {
        $this->dispatch('filter-applied');
    }

    #[Computed]
    public function comments(): Collection|LengthAwarePaginator
    {
        return Repository::allRelatedComments($this->model, $this->limit, $this->sortBy, $this->filter);
    }

    public function render(): View|Factory|Application
    {
        return view(
            'comments::livewire.comment-list',
            ['comments' => $this->comments]
        );
    }
}
