<?php

namespace LakM\Commenter\Reactions;

use LakM\Commenter\Models\Comment;
use LakM\Commenter\Models\Message;
use LakM\Commenter\Models\Reply;

class ReactionManager
{
    public function __construct()
    {
    }

    public function handle(string $type, Message $message, $authMode, mixed $authId): ?bool
    {
        return match ($type) {
            'like' => (new Like(message: $message, authMode:  $authMode, authId: $authId, type: $type))->handle(),
            'dislike' => (new Dislike(message: $message, authMode:  $authMode, authId: $authId, type: $type))->handle(),
            default => (new Reaction(message: $message, authMode:  $authMode, authId:$authId, type: $type))->handle()
        };
    }
}
