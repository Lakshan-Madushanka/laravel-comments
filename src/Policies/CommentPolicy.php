<?php

namespace LakM\Commenter\Policies;

use Illuminate\Contracts\Auth\Authenticatable;
use LakM\Commenter\Models\Comment;
use LakM\Commenter\Policies\Concerns\CanManipulate;

class CommentPolicy
{
    use CanManipulate;

    public function create(): bool
    {
        return true;
    }

    public function update(?Authenticatable $user, Comment $comment, bool $isGuestMode): bool
    {
        return $this->canManipulate($user, $comment, $isGuestMode);
    }

    public function delete(?Authenticatable $user, Comment $comment, bool $isGuestMode): bool
    {
        return $this->canManipulate($user, $comment, $isGuestMode);
    }
}
