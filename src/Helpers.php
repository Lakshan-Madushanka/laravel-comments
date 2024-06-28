<?php

namespace LakM\Comments;

use Illuminate\Contracts\Auth\Authenticatable;
use LakM\Comments\Concerns\Commentable;
use LakM\Comments\Concerns\Commenter;
use LakM\Comments\Contracts\CommentableContract;
use LakM\Comments\Contracts\CommenterContract;
use LakM\Comments\Exceptions\InvalidModelException;

class Helpers
{
    /**
     * @throws \Throwable
     */
    public static function checkCommentableModelValidity(\Illuminate\Database\Eloquent\Model $model): bool
    {
        throw_unless(is_a($model, CommentableContract::class), InvalidModelException::make('Model must use the ' . Commentable::class . ' interface'));

        return true;
    }

    /**
     * @throws \Throwable
     */
    public static function checkCommenterModelValidity(Authenticatable $model): bool
    {
        throw_unless(is_a($model, CommenterContract::class), InvalidModelException::make('Model must use the ' . Commenter::class . ' interface'));

        return true;
    }
}
