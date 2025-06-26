<?php

namespace LakM\Commenter\Livewire\Concerns;

use Illuminate\Support\Str;
use LakM\Commenter\Helpers;

trait HasSingleThread
{
    public function shouldShowSingleThread(): bool
    {
        $query = parse_url(Helpers::livewireCurrentURL(), PHP_URL_QUERY);

        if (empty($query)) {
            return false;
        }

        parse_str($query, $query);

        $query = collect($query);

        if (!($query->has('message_type') && $query->get('message_type') === 'single')) {
            return false;
        }

        if (!($query->has('message_id') && filled($query->get('message_id')))) {
            return false;
        }

        return true;
    }

    public function showFullThread()
    {
        $currentURL = Helpers::livewireCurrentURL();

        $query = parse_url($currentURL, PHP_URL_QUERY);

        if (empty($query)) {
            return false;
        }

        parse_str($query, $query);

        $query = collect($query);

        $query->forget(['message_type', 'message_id']);

        $currentURL = Str::before($currentURL, '?');

        if ($query->isNotEmpty()) {
            $currentURL .= '?' . http_build_query($query->toArray());
        }

        $this->redirect($currentURL);
    }

    public function referencedCommentId(): string
    {
        $query = parse_url(Helpers::livewireCurrentURL())['query'];

        parse_str($query, $query);

        return collect($query)->get('message_id');
    }
}
