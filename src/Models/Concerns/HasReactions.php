<?php

namespace LakM\Comments\Models\Concerns;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use LakM\Comments\ModelResolver;

trait HasReactions
{
    public function reactions(): MorphMany
    {
        return $this->morphMany(ModelResolver::reactionClass(), 'owner');
    }
}
