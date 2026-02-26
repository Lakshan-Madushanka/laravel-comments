<?php

namespace LakM\Commenter;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use LakM\Commenter\Contracts\CommentableContract;
use LakM\Commenter\Contracts\CommenterContract;
use LakM\Commenter\Exceptions\InvalidModelException;
use LakM\Commenter\Facades\SecureGuestMode;
use LakM\Commenter\Models\Comment;
use LakM\Commenter\Models\Message;

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
        return config('commenter.theme') === 'default';
    }

    public static function isGithubTheme(): bool
    {
        return config('commenter.theme') === 'github';
    }

    public static function isModernTheme(): bool
    {
        return config('commenter.theme') === 'modern';
    }

    public static function getAuthGuard(): StatefulGuard
    {
        if (SecureGuestMode::enabled()) {
            return Auth::guard('guest');
        }

        if (config('commenter.auth_guard') === 'default') {
            return Auth::guard(Auth::getDefaultDriver());
        }

        return config('commenter.auth_guard');
    }

    public static function canPinMsg(CommentableContract $commentable, Message $msg): bool
    {
        $configEnabled = $msg instanceof Comment ? config('commenter.pin.enable_comment', true) : config('commenter.pin.enable_reply', false);

        return  Gate::allows('pin-message', [$commentable, $msg]) && $configEnabled;
    }

    public static function getReactionEmoji(string $reaction, bool $isFilled = false): ?string
    {
        if (!$isFilled) {
            return config('commenter.reactions.' . $reaction . '.icon.plain', null);
        }

        return config('commenter.reactions.' . $reaction . '.icon.filled', null);
    }

    /**
     * @param string $viewName
     * @return view-string
     */
    public static function getLivewireViewString(string $viewName): string
    {
        return "commenter::livewire." . $viewName;
    }
}
