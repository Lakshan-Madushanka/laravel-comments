<?php

namespace LakM\Comments\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use LakM\Comments\Models\Concerns\HasOwner;

class Reply extends Model
{
    use HasOwner;

    protected $table = 'comments';

    protected $userRelationshipName = 'commenter';

    protected $fillable = [
        'commenter_type',
        'commenter_id',
        'text',
        'guest_name',
        'guest_email',
        'ip_address',
        'approved',
        'reply_id'
    ];

    protected $casts = [
        'approved' => 'bool'
    ];

    public function comment(): BelongsTo
    {
        return $this->belongsTo(Comment::class);
    }

    public function isEdited(): bool
    {
        return $this->created_at->diffInSeconds($this->updated_at) > 0;
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->whereApproved(true);
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(Reaction::class, 'comment_id');
    }

    public function commenter(): MorphTo
    {
        return $this->morphTo();
    }
}
