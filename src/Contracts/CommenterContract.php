<?php

namespace LakM\Comments\Contracts;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use LakM\Comments\Models\Comment;

interface CommenterContract
{
    /** @return MorphMany<Comment> */
    public function comments(): MorphMany;

    public function replies(): HasMany;

    public function profileUrl(): ?string;

    public function photoUrl(): string;

    public function name(): string;

    public function email(): string;
}
