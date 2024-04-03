<?php

namespace LakM\Comments\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Comment extends Model
{
    protected $fillable = [
        'text',
        'guest_name',
        'guest_email',
        'ip_address',
    ];

    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

}
