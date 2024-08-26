<?php

namespace LakM\Comments\Models\Concerns;

trait HasOwner
{
    public function ownerName(): string
    {
        return $this->{$this->userRelationshipName}->name;
    }
}
