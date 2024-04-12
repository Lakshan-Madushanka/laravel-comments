<?php

namespace LakM\Comments;

use Illuminate\Contracts\Auth\Authenticatable;
use LakM\Comments\Models\Comment;

class CommentPolicy
{
    public function create(): bool
    {
        return true;
    }

    public function update(?Authenticatable $user, Comment $comment): bool
    {
        if (!is_null($user)) {
            return $user->getMorphClass() === $comment->commenter_type &&
                $user->getKey() === $comment->commenter->id;
        }

        return $comment->ip_address === request()->ip();
    }
}
