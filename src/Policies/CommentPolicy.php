<?php

namespace LakM\Comments\Policies;

use Illuminate\Contracts\Auth\Authenticatable;
use LakM\Comments\Models\Comment;

class CommentPolicy
{
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

    private function canManipulate(?Authenticatable $user, Comment $comment, bool $isGuestMode): bool
    {
        if (!$isGuestMode) {
            return $user->getMorphClass() === $comment->commenter_type &&
                $user->getKey() === $comment->commenter->id;
        }

        return $comment->ip_address === request()->ip();
    }
}
