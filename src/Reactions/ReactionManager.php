<?php

namespace LakM\Comments\Reactions;

use LakM\Comments\Models\Comment;

class ReactionManager
{
    public function __construct()
    {
    }

    public function handle(string $type, Comment $comment): ?bool
    {
        return match ($type) {
            'like' => (new Like($comment))->handle(),
            'dislike' => (new Dislike($comment))->handle(),
            default => (new Reaction($comment, $type))->handle()
        };
    }
}
