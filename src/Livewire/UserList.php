<?php

namespace LakM\Comments\Livewire;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use LakM\Comments\Repository;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class UserList extends Component
{
    use WithPagination;

    public bool $show = false;

    public int $perPage;

    public int $limit;

    public $total;

    public string $search = '';

    #[Locked]
    public bool $guestMode;

    public string $editorId;

    public function mount(bool $guestModeEnabled, string $editorId): void
    {
        if (!$this->show) {
            $this->skipRender();
        }

        $this->guestMode = $guestModeEnabled;

        $this->setPerPage();
        $this->limit = $this->perPage;
    }

    public function paginate(): void
    {
        $this->limit += $this->perPage;
    }

    public function setPerPage(): void
    {
        $this->perPage = 15;
    }

    public function loadMore(): void
    {
        if ($this->limit >= $this->total) {
            return;
        }

        $this->limit += $this->perPage;
    }

    #[On('user-mentioned-{editorId}')]
    public function display(string $content, $id): void
    {
        $this->show = true;

        if (!isset($this->total)) {
            $this->total = Repository::usersCount();
        }

        $this->search = $content;
    }

    #[On('user-not-mentioned-{editorId}')]
    public function close(): void
    {
        if (!$this->show) {
            $this->skipRender();
        }

        if ($this->show) {
            $this->show = false;
        }
    }

    public function userSelected(string $name): void
    {
        $this->close();
        $this->dispatch('user-selected-' . $this->editorId, name: $name, editorId: $this->editorId);
    }

    public function render(): View|Factory|Application
    {
        return view('comments::livewire.user-list', ['users' => Repository::usersStartWithName($this->search, $this->guestMode, $this->limit)]);
    }
}
