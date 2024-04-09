<?php

namespace LakM\Comments\Livewire;

use Illuminate\Database\Eloquent\Model;
use LakM\Comments\Repository;
use Livewire\Attributes\Locked;
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

    public ?string $profilePhotoUrl;

    public bool $guestMode;


    public function mount(string $modelClass, mixed $modelId): void
    {
        $this->model = $modelClass::findOrFail($modelId);

        $this->total = Repository::getTotalCommentsCountForRelated($this->model);

        $this->profilePhotoUrl = config('comments.profile_photo_url_column');

        $this->perPage = config('comments.pagination.per_page');
        $this->limit = config('comments.pagination.per_page');

        $this->guestMode = $this->model->guestModeEnabled();
    }

    public function paginate()
    {
        $this->limit += $this->perPage;
    }

    public function render()
    {
        return view('comments::livewire.comment-list',
            ['comments' => Repository::allRelatedComments($this->model, $this->limit)]);
    }
}
