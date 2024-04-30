<?php

namespace LakM\Comments\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        'approved' => 'bool'
    ];

    public function scopeApproved(Builder $query): Builder
    {
        return $query->whereApproved(true);
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
}
