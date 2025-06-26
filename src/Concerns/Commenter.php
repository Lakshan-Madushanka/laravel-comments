<?php

namespace LakM\Commenter\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use LakM\Commenter\ModelResolver;
use LakM\Commenter\Models\Comment;
use LakM\Commenter\Models\Concerns\HasProfilePhoto;
use LakM\Commenter\Models\Concerns\HasReactions;
use LakM\Commenter\Models\Reply;

/**
 * @mixin Model
 */
trait Commenter
{
    use HasProfilePhoto;
    use HasReactions;

    /** @return MorphMany<Comment> */
    public function comments(): MorphMany
    {
        return $this->morphMany(ModelResolver::commentClass(), 'commenter');
    }


    public function profileUrl(): ?string
    {
        if (is_null($url = config('commenter.profile_url_column'))) {
            return null;
        }

        return $this->{$url};
    }

    public function photoUrl(): string
    {
        return $this->ownerPhotoUrl();
    }

    public function name(): string
    {
        return $this->{config('commenter.user_name_column')};
    }

    public function email(): string
    {
        return $this->{config('commenter.user_email_column')};
    }
}
