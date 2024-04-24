<?php

namespace LakM\Comments\Models\Concerns;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Auth;

trait HasOwnerPhoto
{
    protected function ownerPhotoUrl(): Attribute
    {
        return Attribute::make(get: function () {
            $url = "/vendor/lakm/laravel-comments/img/user.png";

            if (Auth::check() && $col = config('comments.profile_photo_url_column')) {
                return $this->{$this->userRelationshipName}->{$col};
            }

            return $url;
        });
    }
}
