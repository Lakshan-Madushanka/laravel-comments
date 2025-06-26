<?php

namespace LakM\Commenter;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\User;
use LakM\Commenter\Builders\MessageBuilder;
use LakM\Commenter\Models\Comment;
use LakM\Commenter\Models\Guest;
use LakM\Commenter\Models\Reaction;

final class ModelResolver
{
    /** @return class-string */
    public static function commentClass(): string
    {
        return  config('commenter.model', Comment::class);
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
        return  config('commenter.guest_model');
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
        return  config('commenter.reaction_model');
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
        return  config('commenter.user_model');
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
