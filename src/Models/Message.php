<?php

namespace LakM\Comments\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Auth\User;
use LakM\Comments\Builders\MessageBuilder;
use LakM\Comments\Contracts\CommenterContract;
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
 * @property (User|Guest)&CommenterContract $commenter
 */
class Message extends Model
{
    use HasOwner;
    use HasProfilePhoto;

    protected $userRelationshipName = 'commenter';

    protected $fillable = [
        'text',
        'commenter_type',
        'commenter_id',
        'approved',
    ];

    protected $casts = [
        'approved' => 'bool',
    ];

    public function getTable()
    {
        return M::commentModel()->table;
    }

    /**
     * @param Builder $query
     * @return MessageBuilder<Message>
     */
    public function newEloquentBuilder($query): MessageBuilder
    {
        return new MessageBuilder($query);
    }

    public function isEdited(): bool
    {
        return $this->created_at->diffInSeconds($this->updated_at) > 0;
    }

    public function commenter(): MorphTo
    {
        return $this->morphTo();
    }

    /** @return HasMany<Reaction> */
    public function reactions(): HasMany
    {
        return $this->hasMany(Reaction::class, 'comment_id');
    }

    public function ownerReactions(): HasMany
    {
        return $this->hasMany(M::reactionClass(), 'comment_id');
    }
}
