<?php

namespace LakM\Commenter\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use LakM\Commenter\Concerns\Commenter;
use LakM\Commenter\Contracts\CommenterContract;
use LakM\Commenter\Data\GuestData;
use LakM\Commenter\Facades\SecureGuestMode;

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
    use Commenter;

    protected $table = 'guests';

    protected $fillable = [
        'name',
        'email',
        'ip_address',
    ];

    public function scopeCreateOrUpdate(Builder $builder, GuestData $data): Model
    {
        if (SecureGuestMode::enabled()) {
            return SecureGuestMode::user();
        }

        return self::query()
            ->updateOrCreate(
                ['ip_address' => request()->ip()],
                array_filter($data->toArray()),
            );
    }
}
