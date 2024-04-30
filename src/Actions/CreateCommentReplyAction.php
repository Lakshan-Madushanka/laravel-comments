<?php

namespace LakM\Comments\Actions;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use LakM\Comments\Events\CommentReplyCreated;
use LakM\Comments\Models\Comment;
use LakM\Comments\Models\Reply;

class CreateCommentReplyAction
{
    /**
     * Create using a custom function
     * @var callable $using
     */
    public static $using;

    /**
     * Model is the commentable model type defined in config
     * @param  Model  $model
     * @param  array  $replyData
     * @return mixed
     */
    public static function execute(Comment $comment, array $replyData, bool $guestMode): mixed
    {
        $replyData = [
            ...$replyData,
            'ip_address' => request()->ip(),
        ];

        if (isset(static::$using)) {
            return static::createUsingCustom($comment, $replyData, $guestMode);
        }

        if ($guestMode) {
            return static::createForGuest($comment, $replyData);
        }

        return self::createForAuthUser($comment, $replyData);
    }

    protected static function createUsingCustom(Model $model, array $replyData, bool $guestMode)
    {
        return call_user_func(self::$using, $model, $replyData, $guestMode);
    }

    protected static function createForGuest(Comment $comment, array $replyData)
    {
        $reply = $comment->replies()->create($replyData);

        self::dispatchEvent($comment, $reply);

        return $reply;
    }

    protected static function createForAuthUser(Comment $comment, array $replyData): Reply
    {
        $user = Auth::guard(config('comments.guard'))
            ->user();

        $reply = $comment->replies()->create([
            ...$replyData,
            'commenter_type' => $user->getMorphClass(),
            'commenter_id' => $user->getAuthIdentifier(),
        ]);

        self::dispatchEvent($comment, $reply);

        return $reply;
    }

    /**
     * @param $comment
     * @return void
     */
    protected static function dispatchEvent(Comment $comment, Reply $reply): void
    {
        Event::dispatch(new CommentReplyCreated($comment, $reply));
    }
}
