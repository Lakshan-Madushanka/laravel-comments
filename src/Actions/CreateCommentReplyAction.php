<?php

namespace LakM\Comments\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use LakM\Comments\Abstracts\AbstractQueries;
use LakM\Comments\Data\GuestData;
use LakM\Comments\Data\MessageData;
use LakM\Comments\Data\UserData;
use LakM\Comments\Events\CommentReplyCreated;
use LakM\Comments\ModelResolver;
use LakM\Comments\Models\Comment;
use LakM\Comments\Models\Reply;

class CreateCommentReplyAction
{
    /**
     * Create using a custom function
     * @var callable|null $using
     */
    public static $using;

    /**
     * Model is the commentable model type defined in config
     * @param Comment $comment
     * @param MessageData $replyData
     * @param bool $guestMode
     * @param GuestData|null $guest
     * @return mixed
     */
    public static function execute(Comment $comment, MessageData $replyData, bool $guestMode, ?GuestData $guest = null): mixed
    {
        if (isset(static::$using)) {
            return static::createUsingCustom($comment, $replyData, $guestMode, $guest);
        }

        if ($guestMode) {
            return static::createForGuest($comment, $replyData, $guest);
        }

        return self::createForAuthUser($comment, $replyData);
    }

    protected static function createUsingCustom(Model $model, MessageData $replyData, bool $guestMode, GuestData $guest)
    {
        return call_user_func(self::$using, $model, $replyData, $guestMode, $guest);
    }

    protected static function createForGuest(Comment $comment, MessageData $replyData, GuestData $guestData)
    {
        $reply = DB::transaction(function () use ($comment, $replyData, $guestData) {
            $guest = ModelResolver::guestClass()::createOrUpdate($guestData);

            $reply = $comment
                ->replies()
                ->create([
                    'text' => $replyData->text,
                    'commenter_type' => $guest->getMorphClass(),
                    'commenter_id' => $guest->getKey(),
                ]);

            if ($guestData->name !== $replyData->name || $guestData->email !== $replyData->email) {
                AbstractQueries::$guest = new UserData($replyData->name, $replyData->email);
            }

            return $reply;
        });

        self::dispatchEvent($comment, $reply);

        return $reply;
    }

    protected static function createForAuthUser(Comment $comment, MessageData $replyData): Reply
    {
        $user = Auth::guard(config('comments.guard'))
            ->user();

        /** @var Reply $reply */
        $reply = $comment
            ->replies()
            ->create([
            'text' => $replyData->text,
            'commenter_type' => ModelResolver::userModel()->getMorphClass(),
            'commenter_id' => $user->getAuthIdentifier(),
        ]);

        self::dispatchEvent($comment, $reply);

        return $reply;
    }

    /**
     * @param Comment $comment
     * @param Reply $reply
     * @return void
     */
    protected static function dispatchEvent(Comment $comment, Reply $reply): void
    {
        Event::dispatch(new CommentReplyCreated($comment, $reply));
    }
}
