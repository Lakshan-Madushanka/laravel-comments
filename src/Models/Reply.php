<?php

namespace LakM\Comments\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use LakM\Comments\Builders\ReplyBuilder;
use LakM\Comments\Model as M;
use LakM\Comments\Models\Concerns\HasOwner;
use LakM\Comments\Models\Concerns\HasProfilePhoto;

/**
 * @property string $commenter_type
 * @property mixed $commenter_id
 * @property string $text
 * @property string $guest_name
 * @property string $guest_email
 * @property string $ip_address
 * @property bool $approved
 * @property mixed $reply_ip
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Reply extends Model
{
    use HasOwner;
    use HasProfilePhoto;

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

    public function newEloquentBuilder($query): ReplyBuilder
    {
        return new ReplyBuilder($query);
    }

    protected $casts = [
        'approved' => 'bool'
    ];

    public function comment(): BelongsTo
    {
        return $this->belongsTo(M::commentClass());
    }

    public function isEdited(): bool
    {
        return $this->created_at->diffInSeconds($this->updated_at) > 0;
    }

    public function ownerReactions(): HasMany
    {
        return $this->reactions();
    }

    /** @return HasMany<Reaction> **/
    public function reactions(): HasMany
    {
        return $this->hasMany(M::reactionClass(), 'comment_id');
    }

    public function commenter(): MorphTo
    {
        return $this->morphTo();
    }
}
