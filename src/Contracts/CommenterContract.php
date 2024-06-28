<?php

namespace LakM\Comments\Contracts;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

interface CommenterContract
{
    public function comments(): MorphMany;

    public function replies(): HasMany;

    public function profileUrl(): false|string;

    public function photoUrl(): string;
}
