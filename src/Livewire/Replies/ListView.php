<?php

namespace LakM\Comments\Livewire\Replies;

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
use LakM\Comments\Models\Message;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * @property \Illuminate\Support\Collection|LengthAwarePaginator $replies
 */
class ListView extends Component
{
    use WithPagination;
    use HasSingleThread;

    public bool $show = false;

    #[Locked]
    public Message $message;

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
     * @param Message $message
     * @param Model&CommentableContract $relatedModel
     * @param int $total
     * @param bool $show
     * @return void
     */
    public function mount(Message $message, Model $relatedModel, int $total, bool $show = false): void
    {
        $this->show = $show;

        if (!$this->show) {
            $this->skipRender();
        }

        $this->message = $message;
        $this->relatedModel = $relatedModel;

        $this->total = $total;
        $this->currentTotal = $total;

        $this->perPage = config('comments.reply.pagination.per_page');
        $this->limit = config('comments.reply.pagination.per_page');

        $this->sortBy = $relatedModel->getRepliesSortOrder();

        $this->guestMode = $this->relatedModel->guestModeEnabled();

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

    #[On('show-replies.{message.id}')]
    public function setShowStatus(): void
    {
        $this->show = !$this->show;

        $this->dispatch('show-reply');
    }

    #[On('reply-created-{message.id}')]
    public function onReplyCreated($messageId): void
    {
        if ($this->approvalRequired) {
            return;
        }

        if ($messageId === $this->message->getKey()) {
            $this->total += 1;
        }
    }

    #[On('reply-deleted-{message.id}')]
    public function onReplyDeleted($messageId): void
    {
        if ($messageId === $this->message->getKey()) {
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
                $this->message,
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
            'comments::livewire.replies.list-view',
            ['replies' => $this->replies]
        );
    }
}
