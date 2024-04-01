<?php

namespace LakM\Comments\concerns;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

/**
 * @mixin Model
 */
trait Commentable
{
    public function comments(): MorphMany
    {
        return $this->morphMany(config('comments.model'), 'commentable');
    }

    public function authCheck(): bool
    {
        return Auth::guard($this->getAuthGuard())->check();
    }

    public function getAuthGuard()
    {
        if (config('comments.auth_guard') === 'default') {
            return Auth::getDefaultDriver();
        }

        return config('auth.defaults.guard');
    }

    /**
     * @throws \Throwable
     */
    public function canCreateComment(): bool
    {
        if (method_exists($this, 'commentCanCreate')) {
            return $this->commentCanCreate();
        }

        if ($this->guestModeEnabled()) {
            return true;
        }

        throw_unless(
            $this->authCheck(),
            new AuthenticationException(guards: [$this->getAuthGuard()], redirectTo: config('login_route'))
        );

        return Gate::allows('create-comment');
    }

    public function guestModeEnabled(): bool
    {
        if (property_exists($this, 'guestMode')) {
            return $this->guestMode;
        }

        if (config('comments.guest_mode')) {
            return true;
        }

        return false;
    }
}
