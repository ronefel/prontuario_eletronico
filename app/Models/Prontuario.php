<?php

namespace App\Models;

use App\Enums\ProntuarioTipoEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prontuario extends Model
{
    use HasFactory;

    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }


    protected $casts = [
        'data' => 'date:Y-m-d',
        'tipo' => ProntuarioTipoEnum::class
    ];
}
