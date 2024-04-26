<?php

namespace LakM\Comments\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;
use LakM\Comments\Events\CommentDeleted;
use LakM\Comments\Models\Comment;

class DeleteCommentAction
{
    /**
     * Create using a custom function
     * @var callable $using
     */
    public static $using;

    public static function execute(Comment $comment): bool
    {
        if (isset(static::$using)) {
            return static::deleteUsingCustom($comment);
        }

        if ($comment->delete()) {

            Event::dispatch(new CommentDeleted($comment));

            return true;
        }

        return false;
    }

    protected static function deleteUsingCustom(Comment $comment, Model $relatedModel)
    {
        return call_user_func_array(self::$using, [$comment, $relatedModel]);
    }
}
