<?php

namespace App\Models;

use App\Casts\DatetimeWithTimezone;
use Stancl\Tenancy\Database\Models\Domain as BaseDomain;

class Domain extends BaseDomain
{
    protected $fillable = [
        'domain',
        'tenant_id',
    ];

    protected $casts = [
        'created_at' => DatetimeWithTimezone::class,
        'updated_at' => DatetimeWithTimezone::class,
    ];
}
