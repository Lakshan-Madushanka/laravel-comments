<?php

namespace LakM\Comments\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;
use LakM\Comments\Events\CommentReplyUpdated;
use LakM\Comments\Models\Comment;
use LakM\Comments\Models\Reply;

class UpdateCommentReplyAction
{
    /**
     * Create using a custom function
     * @var callable $using
     */
    public static $using;

    public static function execute(Reply $reply, array $data): bool
    {
        if (isset(static::$using)) {
            return static::updateUsingCustom($reply);
        }

        $reply->text = $data['text'];
        $reply->approved = false;

        if ($reply->isDirty('text')) {
            $updated = $reply->save();

            Event::dispatch(new CommentReplyUpdated($reply));

            return $updated;
        }

        return false;
    }

    protected static function updateUsingCustom(Comment $reply, Model $relatedModel)
    {
        return call_user_func_array(self::$using, [$reply, $relatedModel]);
    }
}
