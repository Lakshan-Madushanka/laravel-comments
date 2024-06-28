<?php

namespace LakM\Comments\Actions;

use Illuminate\Support\Facades\Event;
use LakM\Comments\Events\CommentReplyUpdated;
use LakM\Comments\Models\Reply;

class UpdateCommentReplyAction
{
    /**
     * Create using a custom function
     * @var callable|null $using
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

    protected static function updateUsingCustom(Reply $reply)
    {
        return call_user_func(self::$using, $reply);
    }
}
