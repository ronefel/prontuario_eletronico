<?php

namespace App\Models;

use App\Casts\DatetimeWithTimezone;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;

    protected $fillable = [
        'id',
        'nome',
        'cnpj',
        'email',
        'telefone',
        'ativo',
        'data',
    ];

    protected $casts = [
        'ativo' => 'boolean',
        'data' => 'array',
        'created_at' => DatetimeWithTimezone::class,
        'updated_at' => DatetimeWithTimezone::class,
    ];

    public static function getCustomColumns(): array
    {
        return [
            'id',
            'nome',
            'cnpj',
            'email',
            'telefone',
            'ativo',
        ];
    }
}
