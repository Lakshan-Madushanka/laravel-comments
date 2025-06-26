<?php

namespace LakM\Commenter\Actions\Comment;

use Illuminate\Support\Facades\Event;
use LakM\Commenter\Events\Comment\CommentUpdated;
use LakM\Commenter\Models\Comment;

class UpdateAction
{
    /**
     * Create using a custom function
     * @var callable|null $using
     */
    public static $using;

    public static function execute(Comment $comment, array $data): bool
    {
        if (isset(static::$using)) {
            return static::updateUsingCustom($comment);
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

    protected static function updateUsingCustom(Comment $comment)
    {
        return call_user_func(self::$using, $comment);
    }
}
