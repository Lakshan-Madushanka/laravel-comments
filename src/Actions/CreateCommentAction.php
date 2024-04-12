<?php

namespace LakM\Comments\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use LakM\Comments\Events\CommentCreated;
use LakM\Comments\Models\Comment;

class CreateCommentAction
{
    /**
     * Create using a custom function
     * @var callable $using
     */
    public static $using;

    /**
     * Model is the commentable model type defined in config
     * @param  Model  $model
     * @param  array  $commentData
     * @return mixed
     */
    public static function execute(Model $model, array $commentData): mixed
    {
        $commentData = [
            ...$commentData,
            'ip_address' => request()->ip(),
        ];

        if (isset(static::$using)) {
            return static::createUsingCustom($model, $commentData);
        }

        if ($model->guestModeEnabled()) {
            return static::createForGuest($model, $commentData);
        }

        return self::createForAuthUser($model, $commentData);
    }

    protected static function createUsingCustom(Model $model, array $commentData)
    {
        return call_user_func(self::$using, $model, $commentData);
    }

    protected static function createForGuest(Model $model, array $commentData)
    {
        $comment =  $model->comments()->create($commentData);

         self::dispatchEvent($comment);

         return $comment;
    }

    protected static function createForAuthUser(Model $model, array $commentData)
    {
        $comment = $model->comments()->create($commentData);
        Auth::guard(config('comments.guard'))
            ->user()
            ->comments()
            ->save($comment);

        $comment->refresh();

        self::dispatchEvent($comment);

        return $comment;
    }

    /**
     * @param $comment
     * @return void
     */
    protected static function dispatchEvent(Comment $comment): void
    {
        Event::dispatch(new CommentCreated($comment));
    }
}
