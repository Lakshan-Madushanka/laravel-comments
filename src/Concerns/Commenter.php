<?php

namespace LakM\Comments\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use LakM\Comments\Models\Reply;

/**
 * @mixin Model
 */
trait Commenter
{
    public function comments(): MorphMany
    {
        return $this->morphMany(config('comments.model'), 'commenter');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(Reply::class);
    }
}
