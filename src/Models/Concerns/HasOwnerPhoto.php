<?php

namespace LakM\Comments\Models\Concerns;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Auth;

trait HasOwnerPhoto
{
    public function ownerPhotoUrl(bool $authMode): string
    {
        $url = "/vendor/lakm/laravel-comments/img/user.png";

        if ($authMode && $col = config('comments.profile_photo_url_column')) {
            return $this->{$this->userRelationshipName}->{$col};
        }

        return $url;
    }

    public function ownerName(bool $authMode): string
    {
        if ($authMode) {
            return $this->{$this->userRelationshipName}->name;
        }

        return $this->name;
    }
}
