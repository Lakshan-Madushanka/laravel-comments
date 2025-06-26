<?php

namespace LakM\Commenter\Events\Comment;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use LakM\Commenter\Models\Comment;

class CommentDeleted
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * Model is the commentable model type defined in config
     * @param  Comment  $model
     */
    public function __construct(public Comment $model)
    {
    }
}
