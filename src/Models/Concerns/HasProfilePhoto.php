<?php

namespace LakM\Comments\Models\Concerns;

trait HasProfilePhoto
{
    public function ownerPhotoUrl(): string
    {
        $col = config('comments.profile_photo.url_column');

        if ($col) {
            if (isset($this->userRelationshipName)) {
                if ($url = $this->getRelation($this->userRelationshipName)->{$col}) {
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

        if (isset($this->userRelationshipName)) {
            $email = $this->getRelation($this->userRelationshipName)->email;
        } else {
            throw_unless(isset($this->email), new \Exception('Couldn\'t find email'));
            $email = $this->email;
        }

        $hash = hash("sha256", strtolower(trim($email)));
        $d = config('comments.profile_photo.default.gravatar.default');

        return "https://gravatar.com/avatar/{$hash}?d={$d}";
    }
}
