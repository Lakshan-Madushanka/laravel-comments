<?php

namespace LakM\Comments\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use LakM\Comments\Models\Message;
use LakM\Comments\Models\Reply;

class ReplyCreated
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(public Message $message, public Reply $reply)
    {
    }
}
