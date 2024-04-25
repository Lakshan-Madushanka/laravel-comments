<?php

namespace LakM\Comments\Reactions;

use LakM\Comments\Models\Comment;

class ReactionManager
{
    public function __construct()
    {
    }

    public function handle(string $type, Comment $comment, $authMode): ?bool
    {
        return match ($type) {
            'like' => (new Like($comment, $authMode))->handle(),
            'dislike' => (new Dislike($comment, $authMode))->handle(),
            default => (new Reaction($comment, $authMode, $type))->handle()
        };
    }
}
