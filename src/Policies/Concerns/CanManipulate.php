<?php

namespace LakM\Comments\Policies\Concerns;

use Illuminate\Contracts\Auth\Authenticatable;
use LakM\Comments\Models\Comment;
use LakM\Comments\Models\Reply;

trait CanManipulate
{
    private function canManipulate(?Authenticatable $user, Reply|Comment $message, bool $isGuestMode): bool
    {
        if (!$isGuestMode && $user) {
            return $user->getMorphClass() === $message->commenter_type &&
                $user->getKey() === $message->commenter->id;
        }

        if (!$isGuestMode && is_null($user)) {
            return false;
        }

        return $message->commenter->ip_address === request()->ip();
    }
}
