<?php

namespace LakM\Commenter\Policies\Concerns;

use Illuminate\Contracts\Auth\Authenticatable;
use LakM\Commenter\Facades\SecureGuestMode;
use LakM\Commenter\Models\Comment;
use LakM\Commenter\Models\Reply;

trait CanManipulate
{
    private function canManipulate(?Authenticatable $user, Reply|Comment $message, bool $isGuestMode): bool
    {
        if (!$isGuestMode) {
            if (is_null($user)) {
                return false;
            }

            return $user->getMorphClass() === $message->commenter_type &&
                $user->getKey() === $message->commenter->getKey();
        }

        if (SecureGuestMode::enabled()) {
            return SecureGuestMode::user()?->getKey() === $message->commenter_id &&
                SecureGuestMode::user()?->getMorphClass() === $message->commenter_type;
        }

        $ipAddress = $message->commenter->ip_address ?? '';

        return $ipAddress === request()->ip();
    }
}
