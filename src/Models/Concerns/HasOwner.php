<?php

namespace LakM\Comments\Models\Concerns;

trait HasOwner
{
    public function ownerPhotoUrl(bool $authMode): string
    {
        $url = "/vendor/lakm/laravel-comments/img/user.png";

        if ($authMode && $col = config('comments.profile_photo_url_column')) {
            return $this->{$this->userRelationshipName}->{$col} ?? $url;
        }

        return $url;
    }

    public function ownerName(bool $authMode): string
    {
        if ($authMode) {
            return $this->{$this->userRelationshipName}->name;
        }

        return $this->guest_name;
    }
}
