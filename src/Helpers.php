<?php

namespace LakM\Comments;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use LakM\Comments\Contracts\CommentableContract;
use LakM\Comments\Contracts\CommenterContract;
use LakM\Comments\Exceptions\InvalidModelException;

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

    public static function getAuthGuard(): StatefulGuard
    {
        if (config('comments.auth_guard') === 'default') {
            return Auth::guard(Auth::getDefaultDriver());
        }

        return config('comments.auth_guard');
    }
}
