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

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'timezone',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
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
}
