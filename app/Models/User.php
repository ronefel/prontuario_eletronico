<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Casts\DatetimeWithTimezone;
use Attribute;
use Carbon\Carbon;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string $timezone
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 *
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTimezone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'timezone',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            // 'created_at' => DatetimeWithTimezone::class,
            // 'updated_at' => DatetimeWithTimezone::class
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    public function createdAt()
    {
        return new Attribute(
            get: function ($value) {
                $date = Carbon::make($value);

                if (Auth::check()) {
                    $date->setTimezone(Auth::user()->timezone);
                }

                return $date;
            },
            set: function ($value) {
                if (Auth::check()) {
                    return Carbon::parse($value, Auth::user()->timezone)->setTimezone('UTC');
                }

                return $value;
            }
        );
    }

    public function updatedAt()
    {
        return new Attribute(
            get: function ($value) {
                $date = Carbon::make($value);

                if (Auth::check()) {
                    $date->setTimezone(Auth::user()->timezone);
                }

                return $date;
            },
            set: function ($value) {
                if (Auth::check()) {
                    return Carbon::parse($value, Auth::user()->timezone)->setTimezone('UTC');
                }

                return $value;
            }
        );
    }

    public static function getAvailableTimezones()
    {
        return [
            'America/Manaus' => 'America/Manaus (UTC -4)',
            'America/Rio_Branco' => 'America/Rio_Branco (UTC -5)',
            'America/Sao_Paulo' => 'America/Sao_Paulo (UTC -3)',
        ];
    }
}
