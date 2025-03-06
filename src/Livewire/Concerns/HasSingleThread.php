<?php

namespace LakM\Comments\Livewire\Concerns;

use Illuminate\Support\Str;
use LakM\Comments\Helpers;

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

        if (!($query->has('commenter_type') && $query->get('commenter_type') === 'single')) {
            return false;
        }

        if (!($query->has('comment_id') && filled($query->get('comment_id')))) {
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

        $query->forget(['commenter_type', 'comment_id']);

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

        return collect($query)->get('comment_id');
    }
}
