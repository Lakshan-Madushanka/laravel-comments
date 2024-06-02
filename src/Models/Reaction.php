<?php

namespace LakM\Comments\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
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

    public function scopeCheckMode(Builder $query, bool $authMode): Builder
    {
        return $query->when(
            $authMode,
            function (Builder $query) {
                return $query->authMode();
            },
            function (Builder $query) {
                return $query->guestMode();
            }
        );
    }

    public function scopeGuestMode(Builder $query): Builder
    {
        return $query->where('user_id', null)
            ->where('ip_address', request()->ip());
    }

    public function scopeAuthMode(Builder $query): Builder
    {
        return $query->where('user_id', Auth::id());
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('comments.user_model'));
    }
}
