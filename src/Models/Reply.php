<?php

namespace LakM\Comments\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use LakM\Comments\Models\Concerns\HasOwner;

class Reply extends Model
{
    use HasOwner;

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
        return $this->belongsTo(Comment::class);
    }

    public function commenter(): MorphTo
    {
        return $this->morphTo();
    }
}
