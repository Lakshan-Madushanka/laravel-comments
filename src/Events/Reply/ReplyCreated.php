<?php

namespace LakM\Commenter\Events\Reply;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use LakM\Commenter\Models\Message;
use LakM\Commenter\Models\Reply;

class ReplyCreated
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(public Message $message, public Reply $reply)
    {
    }
}
