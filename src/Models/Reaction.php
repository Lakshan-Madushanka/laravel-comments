<?php

namespace LakM\Comments\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use LakM\Comments\Builders\ReactionBuilder;
use LakM\Comments\Models\Concerns\HasOwner;
use LakM\Comments\Models\Concerns\HasProfilePhoto;

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

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('comments.user_model'));
    }
}
