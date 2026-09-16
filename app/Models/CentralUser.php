<?php

namespace App\Models;

use App\Casts\DatetimeWithTimezone;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class CentralUser extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable;

    protected $table = 'central_users';

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

    protected function casts(): array
    {
        return [
            'email_verified_at' => DatetimeWithTimezone::class,
            'password' => 'hashed',
            'created_at' => DatetimeWithTimezone::class,
            'updated_at' => DatetimeWithTimezone::class,
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $panel->getId() === 'central';
    }
}
