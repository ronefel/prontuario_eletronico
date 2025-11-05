<?php

namespace App\Models;

class Tratamento extends BaseModel
{
    public function aplicacoes()
    {
        return $this->hasMany(Aplicacao::class);
    }

    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }
}
