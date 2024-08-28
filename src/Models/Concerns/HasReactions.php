<?php

namespace LakM\Comments\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use LakM\Comments\Builders\CommentBuilder;
use LakM\Comments\Contracts\CommentableContract;
use LakM\Comments\ModelResolver;
use LakM\Comments\ModelResolver as M;
use LakM\Comments\Models\Comment;

trait HasReactions
{
    public function reactions(): MorphMany
    {
        return $this->morphMany(ModelResolver::reactionClass(), 'owner');
    }
}
