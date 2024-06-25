<?php

namespace LakM\Comments\Livewire;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use LakM\Comments\Exceptions\InvalidEditorID;
use Livewire\Attributes\Modelable;
use Livewire\Component;

class Editor extends Component
{
    #[Modelable]
    public string $text = '';

    public string $id;

    public string $editorId;
    public string $toolbarId;

    public bool $guestModeEnabled;

    /**
     * @throws \Throwable
     */
    public function mount(string $editorId, bool $guestModeEnabled): void
    {
        throw_unless(Str::isUuid($editorId), new InvalidEditorID());

        $this->editorId = 'editor-' . $editorId;
        $this->toolbarId = 'toolbar-' . $editorId;

        $this->id = $editorId;

        $this->guestModeEnabled = $guestModeEnabled;
    }

    public function render(): View|Factory|Application
    {
        return view('comments::livewire.editor');
    }
}
