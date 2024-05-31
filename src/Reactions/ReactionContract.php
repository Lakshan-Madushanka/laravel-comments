<?php

namespace LakM\Comments\Reactions;

use LakM\Comments\Models\Comment;
use LakM\Comments\Models\Reply;

abstract class ReactionContract
{
    public function __construct(
        protected Reply|Comment $comment,
        protected bool $authMode,
        protected mixed $authId,
        protected ?string $type = null
    ) {
    }
}
