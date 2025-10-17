<?php

namespace LakM\Commenter\Policies;

use Illuminate\Contracts\Auth\Authenticatable;
use LakM\Commenter\Contracts\CommentableContract;
use LakM\Commenter\Models\Message;


class PinMessagePolicy
{
    public function pin(?Authenticatable $user, CommentableContract $commentable, Message $msg): bool
    {
        return false;
    }
}
