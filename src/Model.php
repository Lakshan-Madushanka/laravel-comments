<?php

namespace LakM\Comments;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use LakM\Comments\Models\Comment;
use LakM\Comments\Models\Reaction;

final class Model
{
    /** @return class-string */
    public static function commentClass(): string
    {
        return  config('comments.model');
    }

    public static function commentModel(): Comment
    {
        return  app(self::commentClass());
    }

    public static function commentQuery(): Builder
    {
        return self::commentModel()->newQuery();
    }

    /** @return class-string */
    public static function reactionClass(): string
    {
        return  config('comments.reaction_model');
    }

    public static function reactionModel(): Reaction
    {
        return  app(self::reactionClass());
    }

    public static function reactionQuery(): Builder
    {
        return self::reactionModel()->newQuery();
    }

    public static function userModel(): Authenticatable
    {
        return  app(config('comments.user_model'));
    }

    public static function userQuery(): Builder
    {
        return self::userModel()->newQuery();
    }
}
