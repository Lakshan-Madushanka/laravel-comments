<?php

namespace LakM\Comments\Policies;

use Illuminate\Contracts\Auth\Authenticatable;
use LakM\Comments\Models\Reply;

class ReplyPolicy
{
    public function create(): bool
    {
        return true;
    }

    public function update(?Authenticatable $user, Reply $reply, bool $isGuestMode): bool
    {
        return $this->canManipulate($user, $reply, $isGuestMode);
    }

    public function delete(?Authenticatable $user, Reply $reply, bool $isGuestMode): bool
    {
        return $this->canManipulate($user, $reply, $isGuestMode);
    }

    private function canManipulate(?Authenticatable $user, Reply $reply, bool $isGuestMode): bool
    {
        if (!$isGuestMode) {
            return $user->getMorphClass() === $reply->commenter_type &&
                $user->getKey() === $reply->commenter->id;
        }

        return $reply->ip_address === request()->ip();
    }
}
