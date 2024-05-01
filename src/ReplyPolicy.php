<?php

namespace LakM\Comments;

use Illuminate\Contracts\Auth\Authenticatable;
use LakM\Comments\Models\Reply;

class ReplyPolicy
{
    public function create(): bool
    {
        return true;
    }

    public function update(?Authenticatable $user, Reply $reply): bool
    {
        return $this->canManipulate($user, $reply);
    }

    public function delete(?Authenticatable $user, Reply $reply): bool
    {
        return $this->canManipulate($user, $reply);
    }

    private function canManipulate(?Authenticatable $user, Reply $reply): bool
    {
        if (!is_null($user)) {
            return $user->getMorphClass() === $reply->commenter_type &&
                $user->getKey() === $reply->commenter->id;
        }

        return $reply->ip_address === request()->ip();
    }
}
