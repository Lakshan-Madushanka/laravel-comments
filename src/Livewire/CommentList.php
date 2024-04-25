<?php

namespace LakM\Comments\Livewire;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
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

    public function mount(Model $model): void
    {
        $this->model = $model;

        $this->total = Repository::getTotalCommentsCountForRelated($this->model);

        $this->perPage = config('comments.pagination.per_page');
        $this->limit = config('comments.pagination.per_page');

        $this->guestMode = $this->model->guestModeEnabled();
    }

    public function paginate()
    {
        $this->limit += $this->perPage;
    }

    #[On('comment-created')]
    public function increaseCommentCount(): void
    {
        $this->total += 1;
    }

    public function render(): View|Factory|Application
    {
        return view('comments::livewire.comment-list',
            ['comments' => Repository::allRelatedComments($this->model, $this->limit)]);
    }
}
