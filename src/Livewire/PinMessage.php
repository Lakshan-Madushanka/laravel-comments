<?php

namespace LakM\Commenter\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use LakM\Commenter\Actions\PinMessageAction;
use LakM\Commenter\Contracts\CommentableContract;
use LakM\Commenter\Models\Message;
use Livewire\Component;

class PinMessage extends Component
{
    public Message $msg;

    public CommentableContract $commentable;

    public function mount($commentable, $msg): void
    {
        $this->commentable = $commentable;
        $this->msg = $msg;
    }

    public function pin(PinMessageAction $action): void
    {
        Gate::authorize('pin-message', [$this->commentable, $this->msg]);

        $action->execute($this->commentable, $this->msg);

        $this->dispatch('message-pinned');
    }

    public function render(): View
    {
        return view('commenter::livewire.pin-message');
    }
}
