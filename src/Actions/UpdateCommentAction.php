<?php

namespace LakM\Comments\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;
use LakM\Comments\Events\CommentUpdated;
use LakM\Comments\Models\Comment;

class UpdateCommentAction
{
    /**
     * Create using a custom function
     * @var callable $using
     */
    public static $using;

    public static function execute(Comment $comment, array $data,): bool
    {
        if (isset(static::$using)) {
            return static::createUsingCustom($comment);
        }

        $comment->text = $data['text'];
        $comment->approved = false;

        if ($comment->isDirty('text')) {
            $updated = $comment->save();

            Event::dispatch(new CommentUpdated($comment));

            return $updated;
        }

        return false;
    }

    protected static function createUsingCustom(Comment $comment, Model $relatedModel)
    {
        return call_user_func_array(self::$using, [$comment, $relatedModel]);
    }
}