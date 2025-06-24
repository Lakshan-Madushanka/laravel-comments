<?php

namespace LakM\Comments\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\DB;
use LakM\Comments\Builders\MessageBuilder;
use LakM\Comments\Contracts\CommenterContract;
use LakM\Comments\ModelResolver as M;
use LakM\Comments\Models\Concerns\HasOwner;
use LakM\Comments\Models\Concerns\HasProfilePhoto;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

/**
 * @property string $text
 * @property string $guest_name
 * @property string $guest_email
 * @property string $ip_address
 * @property bool $approved
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property (User|Guest)&CommenterContract $commenter
 *
 * @method MessageBuilder<Comment> repliesCount()
 *
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

    public function scopeRepliesCount(EloquentBuilder $query): EloquentBuilder
    {
        $messageTable = $this->getTable();
        $morphClass = $this->getMorphClass();

        return $query->addSelect([
            'replies_count' => DB::table("$messageTable as c2")
                ->selectRaw('count(*)')
                ->whereColumn('c2.reply_id', "$messageTable.id")
                ->where('c2.reply_type', $morphClass)
        ]);
    }

    public function isEdited(): bool
    {
        return $this->created_at->diffInSeconds($this->updated_at) > 0;
    }

    public function commenter(): MorphTo
    {
        return $this->morphTo();
    }

    public function replies(): MorphMany
    {
        return $this->morphMany(Reply::class, 'reply');
    }

    /** @return HasMany<Reaction> */
    public function reactions(): HasMany
    {
        return $this->hasMany(M::reactionClass(), 'comment_id');
    }

    public function ownerReactions(): HasMany
    {
        return $this->hasMany(M::reactionClass(), 'comment_id');
    }
}
