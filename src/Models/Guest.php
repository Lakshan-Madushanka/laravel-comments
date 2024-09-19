<?php

namespace LakM\Comments\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use LakM\Comments\Concerns\Commenter;
use LakM\Comments\Contracts\CommenterContract;
use LakM\Comments\Data\GuestData;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * @property string $name
 * @property string $email
 * @property string $ip_address
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @method static Model createOrUpdate(GuestData $data)
 */
class Guest extends Authenticatable implements CommenterContract
{
//    use HasProfilePhoto;
//    use HasReactions;
use Commenter;

    protected $table = 'guests';

    protected $fillable = [
        'name',
        'email',
        'ip_address',
    ];

    public function scopeCreateOrUpdate(Builder $builder, GuestData $data): Model
    {
        $newData = $data->toArray();

        if (!$data->name) {
            unset($newData['name']);
        }

        if (!$data->email) {
            unset($newData['email']);
        }

        return self::query()
            ->updateOrCreate(
                ['ip_address' => request()->ip()],
                $newData,
            );
    }

//    public function comments()
//    {
//        return $this->morphMany(ModelResolver::commentClass(), 'commenter');
//    }
}
