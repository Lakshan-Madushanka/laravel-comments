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

        $name = $this->{$this->userRelationshipName}->{$col};

        if (empty($name)) {
            $name = config('comments.replace_null_name', '?');
        }

        return $name;
    }
}
