<?php

namespace LakM\Comments\Models\Concerns;

trait HasProfilePhoto
{
    public function ownerPhotoUrl(bool $authMode): string
    {
        $col = config('comments.profile_photo.url_column');

        if ($authMode && $col) {
            if (isset($this->userRelationshipName)) {
                if ($url = $this->{$this->userRelationshipName}->{$col}) {
                    return $url;
                }
            }

            if ($url = $this->{$col}) {
                return $url;
            }
        }

        if ($url = config('comments.profile_photo.default.url')) {
            return $url;
        }

        $hash = hash("sha256", strtolower(trim($this->guest_email)));
        $d = config('comments.profile_photo.default.gravatar.default');

        return "https://gravatar.com/avatar/{$hash}?d={$d}";
    }
}
