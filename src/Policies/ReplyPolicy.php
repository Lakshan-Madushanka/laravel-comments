<?php

namespace LakM\Comments\Policies;

use Illuminate\Contracts\Auth\Authenticatable;
use LakM\Comments\Models\Reply;
use LakM\Comments\Policies\Concerns\CanManipulate;

class ReplyPolicy
{
    use CanManipulate;

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
}
