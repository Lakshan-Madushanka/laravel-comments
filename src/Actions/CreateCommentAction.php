<?php

namespace LakM\Comments\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use LakM\Comments\Contracts\CommentableContract;
use LakM\Comments\Contracts\CommenterContract;
use LakM\Comments\Data\UserData;
use LakM\Comments\Events\CommentCreated;
use LakM\Comments\Models\Comment;
use LakM\Comments\Queries;

class CreateCommentAction
{
    /**
     * Create using a custom function
     * @var callable|null $using
     */
    public static $using;

    /**
     * @param  Model&CommentableContract  $model
     * @param  array  $commentData
     * @param  UserData|null  $guest
     * @return mixed
     */
    public static function execute(Model $model, array $commentData, ?UserData $guest): mixed
    {
        $commentData = [
            ...$commentData,
            'ip_address' => request()->ip(),
        ];

        if (isset(static::$using)) {
            return static::createUsingCustom($model, $commentData, $guest);
        }

        if ($model->guestModeEnabled()) {
            return static::createForGuest($model, $commentData, $guest);
        }

        return self::createForAuthUser($model, $commentData);
    }

    /**
     * @param  Model&CommentableContract  $model
     * @param  array  $commentData
     * @param  UserData|null  $guest
     * @return mixed
     */
    protected static function createUsingCustom(Model $model, array $commentData, ?UserData $guest): mixed
    {
        return call_user_func(self::$using, $model, $commentData, $guest);
    }

    /**
     * @param  Model&CommentableContract  $model
     * @param  array  $commentData
     * @param  UserData|null  $guest
     * @return Comment
     */
    protected static function createForGuest(Model $model, array $commentData, ?UserData $guest): Comment
    {
        /** @var Comment $comment */
        $comment =  DB::transaction(function () use ($model, $commentData, $guest) {
            $comment =   $model->comments()->create($commentData);

            if ($guest->name !== $commentData['guest_name'] || $guest->email !== $commentData['guest_email']) {
                $user = ['guest_name' => $commentData['guest_name']];

                if ($email = $commentData['guest_email']) {
                    $user['guest_email'] = $email;
                }

                $model->comments()
                    ->where('ip_address', $commentData['ip_address'])
                    ->update($user);

                Queries::$guest = new UserData($commentData['guest_name'], $commentData['guest_email']);
            }

            return $comment;
        });

        self::dispatchEvent($comment);

        return $comment;
    }

    /**
     * @param  Model&CommentableContract  $model
     * @param  array  $commentData
     * @return Comment
     */
    protected static function createForAuthUser(Model $model, array $commentData): Comment
    {
        /** @var Comment $comment */
        $comment = $model->comments()->create($commentData);

        /** @var User&CommenterContract $user**/
        $user = Auth::guard(config('comments.guard'))
            ->user();

        $user->comments()
        ->save($comment);

        $comment->refresh();

        self::dispatchEvent($comment);

        return $comment;
    }

    /**
     * @param  Comment  $comment
     * @return void
     */
    protected static function dispatchEvent(Comment $comment): void
    {
        Event::dispatch(new CommentCreated($comment));
    }
}
