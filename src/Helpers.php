<?php

namespace LakM\Comments;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use LakM\Comments\Contracts\CommentableContract;
use LakM\Comments\Contracts\CommenterContract;
use LakM\Comments\Exceptions\InvalidModelException;
use LakM\Comments\Facades\SecureGuestMode;

class Helpers
{
    /**
     * @throws \Throwable
     */
    public static function checkCommentableModelValidity(Model $model): bool
    {
        throw_unless(is_a($model, CommentableContract::class), InvalidModelException::make('Model must use the ' . CommentableContract::class . ' interface'));

        return true;
    }

    /**
     * @throws \Throwable
     */
    public static function checkCommenterModelValidity(Authenticatable $model): bool
    {
        throw_unless(is_a($model, CommenterContract::class), InvalidModelException::make('Model must use the ' . CommenterContract::class . ' interface'));

        return true;
    }

    public static function isDefaultTheme(): bool
    {
        return config('comments.theme') === 'default';
    }

    public static function isGithubTheme(): bool
    {
        return config('comments.theme') === 'github';
    }

    public static function isModernTheme(): bool
    {
        return config('comments.theme') === 'modern';
    }

    public static function getAuthGuard(): StatefulGuard
    {
        if (SecureGuestMode::enabled()) {
            return Auth::guard('guest');
        }

        if (config('comments.auth_guard') === 'default') {
            return Auth::guard(Auth::getDefaultDriver());
        }

        return config('comments.auth_guard');
    }

    public static function livewireCurrentURL(): string
    {
        if (request()->route()->named('livewire.update')) {
            return URL::previous();
        } else {
            return request()->url();
        }
    }
}
