<?php

namespace LakM\Comments\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use LakM\Comments\Models\Concerns\HasOwner;
use LakM\Comments\Models\Concerns\HasOwnerReactions;
use LakM\Comments\Models\Concerns\HasProfilePhoto;
use LakM\Comments\Model as M;

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

    public function scopeApproved(Builder $query): Builder
    {
        return $query->whereApproved(true);
    }

    public function scopeWithOwnerReactions(Builder $query, Model $relatedModel): Builder
    {
        return $query->with(['ownerReactions' => fn( $query) => $query->checkMode(!$relatedModel->guestModeEnabled())]);
    }

    public function scopeCurrentUser(Builder $query, Model $relatedModel, string $filter): Builder
    {
        $alias = M::userModel()->getMorphClass();

        return $query->when(
            $filter === 'my_comments' && $relatedModel->guestModeEnabled(),
            fn(Builder $query) => $query->where('ip_address', request()->ip())
        )
            ->when(
                $filter === 'my_comments' && !$relatedModel->guestModeEnabled(),
                fn(Builder $query) => $query
                    ->where('commenter_type', $alias)
                    ->where('commenter_id', $relatedModel->getAuthUser()->getAuthIdentifier())
            );
    }

    public function ownerReactions(): HasMany
    {
        return $this->reactions();
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(M::reactionClass(), 'comment_id');
    }

    public function commenter(): MorphTo
    {
        return $this->morphTo();
    }
}
