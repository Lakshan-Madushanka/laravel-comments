<?php

namespace LakM\Comments\concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use LakM\Comments\Models\Comment;

/**
 * @mixin Model
 */
trait Commentable
{
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
}