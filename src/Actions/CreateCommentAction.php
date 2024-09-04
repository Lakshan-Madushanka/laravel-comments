<?php

namespace LakM\Comments\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use LakM\Comments\Abstracts\AbstractQueries;
use LakM\Comments\Contracts\CommentableContract;
use LakM\Comments\Data\GuestData;
use LakM\Comments\Data\MessageData;
use LakM\Comments\Data\UserData;
use LakM\Comments\Events\CommentCreated;
use LakM\Comments\ModelResolver;
use LakM\Comments\Models\Comment;

class CreateCommentAction
{
    /**
     * Create using a custom function
     * @var callable|null $using
     */
    public static $using;

    /**
     * @param Model&CommentableContract $model
     * @param MessageData $commentData
     * @param GuestData $guestData
     * @return mixed
     */
    public static function execute(Model $model, MessageData $commentData, GuestData $guestData): mixed
    {
        if (isset(static::$using)) {
            return static::createUsingCustom($model, $commentData, $guestData);
        }

        if ($model->guestModeEnabled()) {
            return static::createForGuest($model, $commentData, $guestData);
        }

        return self::createForAuthUser($model, $commentData);
    }

    /**
     * @param Model&CommentableContract $model
     * @param MessageData $commentData
     * @param GuestData $guestData
     * @return mixed
     */
    protected static function createUsingCustom(Model $model, MessageData $commentData, GuestData $guestData): mixed
    {
        return call_user_func(self::$using, $model, $commentData, $guestData);
    }

    /**
     * @param Model&CommentableContract $model
     * @param MessageData $commentData
     * @param GuestData $guestData
     * @return Comment
     */
    protected static function createForGuest(Model $model, MessageData $commentData, GuestData $guestData): Comment
    {
        /** @var Comment $comment */
        $comment = DB::transaction(function () use ($model, $commentData, $guestData) {
            $guest = ModelResolver::guestClass()::createOrUpdate($guestData);

            $comment = $model
                ->comments()
                ->create([
                    'text' => $commentData->text,
                    'commenter_type' => $guest->getMorphClass(),
                    'commenter_id' => $guest->getKey(),
                ]);

            if ($guestData->name !== $commentData->name || $guestData->email !== $commentData->email) {
                AbstractQueries::$guest = new UserData($commentData->name, $commentData->email);
            }

            return $comment;
        });

        self::dispatchEvent($comment);

        return $comment;
    }

    /**
     * @param Model&CommentableContract $model
     * @param MessageData $commentData
     * @return Comment
     */
    protected static function createForAuthUser(Model $model, MessageData $commentData): Comment
    {
        /** @var Comment $comment */
        $comment = $model
            ->comments()
            ->create([
                'text' => $commentData->text,
                'commenter_type' => ModelResolver::userModel()->getMorphClass(),
                'commenter_id' => $model->getAuthUser()->getKey(),
            ]);

        self::dispatchEvent($comment);

        return $comment;
    }

    /**
     * @param Comment $comment
     * @return void
     */
    protected static function dispatchEvent(Comment $comment): void
    {
        Event::dispatch(new CommentCreated($comment));
    }
}
