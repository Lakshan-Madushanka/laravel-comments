<?php

namespace LakM\Comments\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CommentCreated
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * Model is the commentable model type defined in config
     * @param $model
     */
    public function __construct($model)
    {
    }

}
