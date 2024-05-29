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

    public function photoUrl(bool $authMode): string
    {
        $url = "/vendor/lakm/laravel-comments/img/user.png";

        if ($authMode && $col = config('comments.profile_photo_url_column')) {
            return $this->{$col} ?? $url;
        }

        return $url;
    }

    public function name(bool $authMode): string
    {
        if ($authMode) {
            return $this->name;
        }

        return $this->guest_name;
    }

    public function isAdminPanelAccesible()
    {
        if (!App::isProduction()) {
            return true;
        }

        if(method_exists($this, 'canAccessAdminPanel')) {
            return $this->canAccessAdminPanel();
        }

        throw new AuthorizationException();
    }
}
