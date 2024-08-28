<?php

namespace LakM\Comments\Models\Concerns;

trait HasOwner
{
    public function ownerName(bool $isAuthMode): string
    {
        if ($isAuthMode) {
            $col = config('comments.user_name_column');
        } else {
            $col = 'name';
        }
        return $this->{$this->userRelationshipName}->{$col};
    }
}
