<?php

namespace LakM\Comments\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * @mixin Model
 */
trait Commenter
{
    public function comments(): MorphMany
    {
        return $this->morphMany(config('comments.model'), 'commenter');
    }
}
