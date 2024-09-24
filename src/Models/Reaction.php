<?php

namespace LakM\Comments\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User;
use LakM\Comments\Builders\ReactionBuilder;
use LakM\Comments\Contracts\CommenterContract;
use LakM\Comments\Models\Concerns\HasOwner;
use LakM\Comments\Models\Concerns\HasProfilePhoto;

/**
 * @property mixed $user_id
 * @property string $ip_address
 * @property string $updated_at
 */
class Reaction extends Model
{
    use HasOwner;
    use HasProfilePhoto;

    protected $userRelationshipName = 'user';

    protected $fillable = [
        'comment_id',
        'type',
        'user_id',
        'ip_address',
    ];

    /**
     * @param $query
     * @return ReactionBuilder<Reaction>
     */
    public function newEloquentBuilder($query): ReactionBuilder
    {
        return new ReactionBuilder($query);
    }

    /** @return BelongsTo<User&CommenterContract, Reaction> **/
    public function user(): BelongsTo
    {
        return $this->belongsTo(config('comments.user_model'));
    }
}
