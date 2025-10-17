<?php

namespace LakM\Commenter\Livewire\Comments;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use LakM\Commenter\Abstracts\AbstractQueries;
use LakM\Commenter\Contracts\CommentableContract;
use LakM\Commenter\Enums\Sort;
use LakM\Commenter\Facades\SecureGuestMode;
use LakM\Commenter\Helpers;
use LakM\Commenter\Livewire\Concerns\HasSingleThread;
use LakM\Commenter\Models\Message;
use LakM\Commenter\Queries;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;
use Throwable;

/**
 * @property Collection|LengthAwarePaginator $comments
 */
#[Lazy]
class ListView extends Component
{
    use WithPagination;
    use HasSingleThread;

    /** @var Model&CommentableContract */
    #[Locked]
    public Model $model;

    public int $total;

    public ?int $limit = 15;

    public ?int $perPage;

    #[Locked]
    public bool $guestMode;

    public bool $paginationRequired;

    public Sort $sortBy;

    public string $filter = '';

    public bool $showReplyList = false;

    /**
     * @param  Model&CommentableContract  $model
     * @return void
     * @throws Throwable
     */
    public function mount(Model $model): void
    {
        Helpers::checkCommentableModelValidity($model);

        $this->model = $model;

        $this->perPage = config('commenter.pagination.per_page');
        $this->limit = config('commenter.pagination.per_page');

        $this->sortBy = $model->getCommentsSortOrder();

        $this->setTotalCommentsCount();

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
        $this->total = $this->comments->count();

        if ($this->comments instanceof LengthAwarePaginator) {
            $this->total = $this->comments->total();
        }
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

        $this->setTotalCommentsCount();
        $this->setPaginationRequired();
        $this->dispatchFilterAppliedEvent();
    }

    public function logOut(): void
    {
        SecureGuestMode::logOut();

        $this->dispatch('logout');
    }

    #[On('comment-created')]
    public function increaseCommentCount(bool $approvalRequired): void
    {
        if ($approvalRequired) {
            return;
        }

        $this->total += 1;
    }

    #[On('comment-deleted')]
    public function onCommentDeleted($commentId): void
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
    public function pinnedMsg(): ?Message
    {
        return Queries::pinnedMsg($this->model);
    }

    #[Computed]
    public function comments(): Collection|LengthAwarePaginator
    {
        if ($this->shouldShowSingleThread()) {
            return app(AbstractQueries::class)->relatedComment($this->model, $this->referencedCommentId(), $this->limit, $this->sortBy, $this->filter);
        }

        return app(AbstractQueries::class)->allRelatedComments($this->model, $this->limit, $this->sortBy, $this->filter);
    }

    public function showAll(): void
    {
        $this->showFullThread();
    }

    public function placeholder(array $params = []): Factory|View|Application
    {
        return view('commenter::components.skeleton', $params);
    }

    public function render(): View|Factory|Application
    {
        return view(
            'commenter::livewire.comments.list-view',
            ['comments' => $this->comments]
        );
    }
}
