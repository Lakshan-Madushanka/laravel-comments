<?php

namespace LakM\Comments\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;
use LakM\Comments\Events\CommentReplyDeleted;
use LakM\Comments\Models\Comment;
use LakM\Comments\Models\Reply;

class DeleteCommentReplyAction
{
    /**
     * Create using a custom function
     * @var callable $using
     */
    public static $using;

    public static function execute(Reply $reply): bool
    {
        if (isset(static::$using)) {
            return static::deleteUsingCustom($reply);
        }

        if ($reply->delete()) {

            Event::dispatch(new CommentReplyDeleted($reply));

            return true;
        }

        return false;
    }

    protected static function deleteUsingCustom(Comment $reply, Model $relatedModel)
    {
        return call_user_func_array(self::$using, [$reply, $relatedModel]);
    }
}
