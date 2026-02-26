<?php

namespace LakM\Commenter\Actions\Reply;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use LakM\Commenter\Abstracts\AbstractQueries;
use LakM\Commenter\Data\GuestData;
use LakM\Commenter\Data\MessageData;
use LakM\Commenter\Data\UserData;
use LakM\Commenter\Events\Reply\ReplyCreated;
use LakM\Commenter\ModelResolver;
use LakM\Commenter\Models\Message;
use LakM\Commenter\Models\Reply;

class CreateAction
{
    /**
     * Create using a custom function
     * @var callable|null $using
     */
    public static $using;

    /**
     * Model is the commentable model type defined in config
     * @param Message $message
     * @param MessageData $replyData
     * @param bool $guestMode
     * @param GuestData|null $guest
     * @return mixed
     */
    public static function execute(Message $message, MessageData $replyData, bool $guestMode, ?GuestData $guest = null): mixed
    {
        if (isset(static::$using)) {
            return static::createUsingCustom($message, $replyData, $guestMode, $guest);
        }

        if ($guestMode) {
            return static::createForGuest($message, $replyData, $guest);
        }

        return self::createForAuthUser($message, $replyData);
    }

    protected static function createUsingCustom(Model $model, MessageData $replyData, bool $guestMode, GuestData $guest)
    {
        return call_user_func(self::$using, $model, $replyData, $guestMode, $guest);
    }

    protected static function createForGuest(Message $message, MessageData $replyData, GuestData $guestData)
    {
        $reply = DB::transaction(function () use ($message, $replyData, $guestData) {
            $guest = ModelResolver::guestClass()::createOrUpdate($guestData);

            /** @var Reply $reply */
            $reply = $message
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

        self::dispatchEvent($message, $reply);

        return $reply;
    }

    protected static function createForAuthUser(Message $message, MessageData $replyData): Reply
    {
        $user = Auth::guard(config('commenter.guard'))
            ->user();

        /** @var Reply $reply */
        $reply = $message
            ->replies()
            ->create([
            'text' => $replyData->text,
            'commenter_type' => ModelResolver::userModel()->getMorphClass(),
            'commenter_id' => $user->getAuthIdentifier(),
        ]);

        self::dispatchEvent($message, $reply);

        return $reply;
    }

    /**
     * @param Message $message
     * @param Reply $reply
     * @return void
     */
    protected static function dispatchEvent(Message $message, Reply $reply): void
    {
        Event::dispatch(new ReplyCreated($message, $reply));
    }
}
