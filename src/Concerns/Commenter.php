<?php

namespace LakM\Comments\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use LakM\Comments\ModelResolver;
use LakM\Comments\Models\Comment;
use LakM\Comments\Models\Concerns\HasProfilePhoto;
use LakM\Comments\Models\Concerns\HasReactions;
use LakM\Comments\Models\Reply;

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

    public function replies(): HasMany
    {
        return $this->hasMany(Reply::class);
    }

    public function profileUrl(): ?string
    {
        if (is_null($url = config('comments.profile_url_column'))) {
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
        return $this->{config('comments.user_name_column')};
    }

    public function email(): string
    {
        return $this->{config('comments.user_email_column')};
    }
}
