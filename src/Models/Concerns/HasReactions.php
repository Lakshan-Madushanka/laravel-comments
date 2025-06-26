<?php

namespace LakM\Commenter\Models\Concerns;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use LakM\Commenter\ModelResolver;

trait HasReactions
{
    public function reactions(): MorphMany
    {
        return $this->morphMany(ModelResolver::reactionClass(), 'owner');
    }
}
