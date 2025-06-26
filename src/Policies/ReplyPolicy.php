<?php

namespace LakM\Commenter\Policies;

use Illuminate\Contracts\Auth\Authenticatable;
use LakM\Commenter\Models\Reply;
use LakM\Commenter\Policies\Concerns\CanManipulate;

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
