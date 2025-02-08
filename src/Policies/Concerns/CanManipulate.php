<?php

namespace LakM\Comments\Policies\Concerns;

use Illuminate\Contracts\Auth\Authenticatable;
use LakM\Comments\Facades\SecureGuestMode;
use LakM\Comments\Models\Comment;
use LakM\Comments\Models\Reply;

trait CanManipulate
{
    private function canManipulate(?Authenticatable $user, Reply|Comment $message, bool $isGuestMode): bool
    {
        if (!$isGuestMode && $user) {
            return $user->getMorphClass() === $message->commenter_type &&
                $user->getKey() === $message->commenter->getKey();
        }

        if (!$isGuestMode && is_null($user)) {
            return false;
        }

        if (SecureGuestMode::enabled()) {
            return SecureGuestMode::user()?->getKey() === $message->commenter_id &&
                SecureGuestMode::user()?->getMorphClass() === $message->commenter_type;
        }

        $ipAddress = $message->commenter->ip_address ?? '';

        return $ipAddress === request()->ip();
    }
}
