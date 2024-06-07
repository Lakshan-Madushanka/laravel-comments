<?php

namespace LakM\Comments\Reactions;

use LakM\Comments\Models\Comment;
use LakM\Comments\Models\Reply;

class ReactionManager
{
    public function __construct()
    {
    }

    public function handle(string $type, Reply|Comment $comment, $authMode, mixed $authId): ?bool
    {
        return match ($type) {
            'like' => (new Like(comment: $comment, authMode:  $authMode, authId: $authId, type: $type))->handle(),
            'dislike' => (new Dislike(comment: $comment, authMode:  $authMode, authId: $authId, type: $type))->handle(),
            default => (new Reaction(comment: $comment, authMode:  $authMode, authId:$authId, type: $type))->handle()
        };
    }
}
