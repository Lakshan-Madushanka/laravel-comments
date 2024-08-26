<?php

namespace LakM\Comments\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use LakM\Comments\ModelResolver;
use LakM\Comments\Models\Concerns\HasOwner;

/**
 * @property string name
 * @property string email
 * @property string $ip_address
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Guest extends Model
{
    use HasOwner;

    protected $table = 'guests';

    protected $fillable = [
        'name',
        'email',
        'ip_address',
    ];

    public function comments()
    {
        return $this->morphMany(ModelResolver::commentClass(), 'commenter');
    }
}
