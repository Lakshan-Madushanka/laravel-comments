<?php

namespace LakM\Comments\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CommentCreated
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * Model is the commentable model type defined in config
     * @param  Model  $model
     */
    public function __construct(public Model $model)
    {
    }

}
