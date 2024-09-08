<?php

namespace LakM\Comments\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use LakM\Comments\Builders\MessageBuilder;
use LakM\Comments\ModelResolver as M;
use LakM\Comments\Models\Concerns\HasOwner;
use LakM\Comments\Models\Concerns\HasProfilePhoto;

/**
 * @property string $text
 * @property string $guest_name
 * @property string $guest_email
 * @property string $ip_address
 * @property bool $approved
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @method MessageBuilder query()
 */
class Comment extends Message
{
    use HasOwner;
    use HasProfilePhoto;

    protected $fillable = [
        'text',
        'commenter_type',
        'commenter_id',
        'approved',
    ];

    public function commentable(): MorphTo
    {
        return $this->morphTo();
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
