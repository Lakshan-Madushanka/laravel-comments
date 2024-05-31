<?php

namespace LakM\Comments\Concerns;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\App;
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

    public function isAdminPanelAccessible()
    {
        if (!App::isProduction()) {
            return true;
        }

        if (method_exists($this, 'canAccessAdminPanel')) {
            return $this->canAccessAdminPanel();
        }

        throw new AuthorizationException();
    }
}
