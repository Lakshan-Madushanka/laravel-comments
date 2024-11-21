<?php

namespace LakM\Comments;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\User;
use LakM\Comments\Builders\MessageBuilder;
use LakM\Comments\Models\Comment;
use LakM\Comments\Models\Guest;
use LakM\Comments\Models\Reaction;

final class ModelResolver
{
    /** @return class-string */
    public static function commentClass(): string
    {
        return  config('comments.model', Comment::class);
    }

    public static function commentModel(): Comment
    {
        return  app(self::commentClass());
    }

    /**
     * @return MessageBuilder<Comment>
     */
    public static function commentQuery(): Builder
    {
        return self::commentModel()->newQuery();
    }

    /** @return class-string */
    public static function guestClass(): string
    {
        return  config('comments.guest_model');
    }

    public static function guestModel(): Guest
    {
        return  app(self::guestClass());
    }

    public static function guestQuery(): Builder
    {
        return self::guestModel()->newQuery();
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

    /** @return class-string */
    public static function userClass(): string
    {
        return  config('comments.user_model');
    }

    public static function userModel(): User
    {
        return  app(self::userClass());
    }

    public static function userQuery(): Builder
    {
        return self::userModel()->newQuery();
    }
}
