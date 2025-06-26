<?php

namespace LakM\Commenter\Contracts;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use LakM\Commenter\Models\Comment;

interface CommenterContract
{
    /** @return MorphMany<Comment> */
    public function comments(): MorphMany;

    public function profileUrl(): ?string;

    public function photoUrl(): string;

    public function name(): string;

    public function email(): string;
}
