<?php

namespace LakM\Comments\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use LakM\Comments\Models\Concerns\HasOwner;

class Comment extends Model
{
    use HasOwner;

    protected $userRelationshipName = 'commenter';

    protected $fillable = [
        'text',
        'guest_name',
        'guest_email',
        'ip_address',
        'approved',
    ];

    protected $casts = [
        'approved' => 'bool',
    ];

    public function scopeApproved(Builder $query): Builder
    {
        return $query->whereApproved(true);
    }

    public function scopecheckApproval(Builder $query, Model $relatedModel): Builder
    {
        return $query->when($relatedModel->approvalRequired(), fn(Builder $query) => $query->approved());
    }

    public function scopeWithCommenter(Builder $query, Model $relatedModel): Builder
    {
        return $query->when(!$relatedModel->guestModeEnabled(), fn(Builder $query) => $query->with('commenter'))
            ;
    }

    public function isEdited(): bool
    {
        return $this->created_at->diffInSeconds($this->updated_at) > 0;
    }

    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function commenter(): MorphTo
    {
        return $this->morphTo();
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(Reaction::class);
    }

    public function replies(): HasMany
    {
        return $this->hasMany(Reply::class, 'reply_id', 'id');
    }

    public function replyReactions(): HasManyThrough
    {
        return $this->hasManyThrough(Reaction::class, Reply::class, 'reply_id', 'comment_id');
    }
}
