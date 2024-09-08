<?php

namespace LakM\Comments\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Foundation\Auth\User;
use LakM\Comments\Builders\ReactionBuilder;
use LakM\Comments\Contracts\CommenterContract;
use LakM\Comments\ModelResolver;
use LakM\Comments\Models\Concerns\HasOwner;
use LakM\Comments\Models\Concerns\HasProfilePhoto;

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
