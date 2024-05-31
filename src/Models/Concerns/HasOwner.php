<?php

namespace LakM\Comments\Models\Concerns;

trait HasOwner
{
    public function ownerName(bool $authMode): string
    {
        if ($authMode) {
            return $this->{$this->userRelationshipName}->name;
        }

        return $this->guest_name;
    }
}
