<?php

namespace App\Models;

use App\Casts\DatetimeWithTimezone;
use App\Enums\ProntuarioTipoEnum;

class Prontuario extends BaseModel
{
    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }


    protected $casts = [
        'data' => DatetimeWithTimezone::class,
        'tipo' => ProntuarioTipoEnum::class
    ];
}
