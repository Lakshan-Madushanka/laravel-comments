<?php

namespace LakM\Comments\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use LakM\Comments\Data\GuestData;
use LakM\Comments\ModelResolver;
use LakM\Comments\Models\Concerns\HasOwner;
use LakM\Comments\Models\Concerns\HasProfilePhoto;
use LakM\Comments\Models\Concerns\HasReactions;

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
    use HasProfilePhoto;
    use HasReactions;

    protected $table = 'guests';

    protected $fillable = [
        'name',
        'email',
        'ip_address',
    ];

    public static function createOrUpdate(GuestData $data): Builder|Model
    {
        return self::query()
            ->updateOrCreate(
                ['ip_address' => request()->ip()],
                [...$data->toArray()]
            );
    }

    public function comments()
    {
        return $this->morphMany(ModelResolver::commentClass(), 'commenter');
    }
}
