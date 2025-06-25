<?php

namespace LakM\Comments\Reactions;

use LakM\Comments\Models\Comment;
use LakM\Comments\Models\Message;
use LakM\Comments\Models\Reply;

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
