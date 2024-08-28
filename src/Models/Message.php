<?php

namespace LakM\Comments\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Query\Builder;
use LakM\Comments\Builders\CommentBuilder;
use LakM\Comments\Builders\MessageBuilder;
use LakM\Comments\Concerns\Commenter;
use LakM\Comments\ModelResolver;
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
 * @method CommentBuilder query()
 */
class Message extends Model
{
    use HasOwner;
    use HasProfilePhoto;

    protected $table = 'comments';

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

    /**
     * @param Builder $query
     * @return MessageBuilder
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
        return $this->hasMany(M::reactionClass(), 'comment_id');
    }

    public function ownerReactions(): HasMany
    {
        return $this->reactions();
    }
}
