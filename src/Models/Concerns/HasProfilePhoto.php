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

        if ($authMode) {
            if (isset($this->userRelationshipName)) {
                $email = $this->{$this->userRelationshipName}->email;
            } else {
                $email = $this->email ?? '';
            }
        } else {
            $email = $this->guest_email ?? '';
        }

        $hash = hash("sha256", strtolower(trim($email)));
        $d = config('comments.profile_photo.default.gravatar.default');

        return "https://gravatar.com/avatar/{$hash}?d={$d}";
    }
}
