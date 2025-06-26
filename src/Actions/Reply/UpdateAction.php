<?php

namespace LakM\Commenter\Actions\Reply;

use Illuminate\Support\Facades\Event;
use LakM\Commenter\Events\Reply\ReplyUpdated;
use LakM\Commenter\Models\Reply;

class UpdateAction
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

            Event::dispatch(new ReplyUpdated($reply));

            return $updated;
        }

        return false;
    }

    protected static function updateUsingCustom(Reply $reply)
    {
        return call_user_func(self::$using, $reply);
    }
}
