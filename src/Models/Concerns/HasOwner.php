<?php

namespace LakM\Commenter\Models\Concerns;

trait HasOwner
{
    public function ownerName(bool $isAuthMode): string
    {
        if ($isAuthMode) {
            $col = config('commenter.user_name_column');
        } else {
            $col = 'name';
        }

        $name = $this->{$this->userRelationshipName}->{$col};

        if (empty($name)) {
            $name = config('commenter.replace_null_name', '?');
        }

        return $name;
    }
}
