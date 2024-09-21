<?php

namespace LakM\Comments\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use LakM\Comments\Builders\CommentBuilder;
use LakM\Comments\ModelResolver as M;
use LakM\Comments\Models\Concerns\HasOwner;
use LakM\Comments\Models\Concerns\HasProfilePhoto;

/**
 * @property ?string $commenter_type
 * @property string $text
 * @property string $guest_name
 * @property string $guest_email
 * @property string $ip_address
 * @property bool $approved
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Comment extends Model
{
    use HasOwner;
    use HasProfilePhoto;

    protected $userRelationshipName = 'commenter';

    protected $fillable = [
        'text',
        'guest_name',
        'guest_email',
        'ip_address',
        'approved',
    ];

    /**
     * @param \Illuminate\Database\Query\Builder $query
     * @return CommentBuilder<Comment>
     */
    public function newEloquentBuilder($query): CommentBuilder
    {
        return new CommentBuilder($query);
    }

    protected $casts = [
        'approved' => 'bool',
    ];

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

    /** @return HasMany<Reaction> */
    public function reactions(): HasMany
    {
        return $this->hasMany(M::reactionClass());
    }

    public function ownerReactions(): HasMany
    {
        return $this->reactions();
    }

    /**
     * @return HasMany
     */
    public function replies(): HasMany
    {
        return $this->hasMany(Reply::class, 'reply_id', 'id');
    }

    public function replyReactions(): HasManyThrough
    {
        return $this->hasManyThrough(M::reactionClass(), Reply::class, 'reply_id', 'comment_id');
    }
}
