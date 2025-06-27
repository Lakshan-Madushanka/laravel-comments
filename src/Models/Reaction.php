<?php

namespace LakM\Commenter\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Foundation\Auth\User;
use LakM\Commenter\Builders\ReactionBuilder;
use LakM\Commenter\Contracts\CommenterContract;
use LakM\Commenter\ModelResolver;
use LakM\Commenter\Models\Concerns\HasOwner;
use LakM\Commenter\Models\Concerns\HasProfilePhoto;

/**
 * @method static ReactionBuilder|static query()
 */
class Reaction extends Model
{
    use HasOwner;
    use HasProfilePhoto;

    protected string $userRelationshipName = 'owner';

    protected $fillable = [
        'comment_id',
        'type',
        'owner_id',
        'owner_type',
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
        return $this->belongsTo(ModelResolver::userModel());
    }

    public function owner(): MorphTo
    {
        return $this->morphTo();
    }
}
