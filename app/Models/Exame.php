<?php

namespace App\Models;

use App\Casts\DatetimeWithTimezone;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Exame extends BaseModel
{
    use HasFactory;

    protected $casts = [
        'data' => DatetimeWithTimezone::class,
    ];

    public function testadores()
    {
        return $this->belongsToMany(Testador::class, 'exame_testador');
    }

    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }
}
