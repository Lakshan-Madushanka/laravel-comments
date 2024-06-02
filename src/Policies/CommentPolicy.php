<?php

namespace LakM\Comments\Policies;

use Illuminate\Contracts\Auth\Authenticatable;
use LakM\Comments\Models\Comment;
use LakM\Comments\Policies\Concerns\CanManipulate;

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
