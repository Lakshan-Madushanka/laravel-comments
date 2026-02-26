<?php

namespace LakM\Commenter\Contracts;

use Illuminate\Database\Eloquent\Relations\MorphMany;


interface CommenterContract
{
    public function comments(): MorphMany;

    public function profileUrl(): ?string;

    public function photoUrl(): string;

    public function name(): string;

    public function email(): string;
}
