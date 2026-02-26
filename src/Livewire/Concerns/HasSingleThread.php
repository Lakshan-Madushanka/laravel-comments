<?php

namespace LakM\Commenter\Livewire\Concerns;

use Livewire\Attributes\Url;
use Livewire\Livewire;

trait HasSingleThread
{
    #[Url]
    public string $message_type = '';

    #[Url]
    public string $message_id = '';

    public function shouldShowSingleThread(): bool
    {
        if ($this->message_id && $this->message_type === 'single') {
            return true;
        }

        return false;
    }

    public function showFullThread(): void
    {
        $this->redirect(Livewire::originalUrl());
    }

    public function referencedCommentId(): string
    {
        return $this->message_id;
    }
}
