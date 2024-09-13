<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Cidade extends BaseModel
{
    use HasFactory;

    public function cidadeUf()
    {
        return $this->nome . ' - ' . $this->uf;
    }

    public function pacientes()
    {
        return $this->hasMany(Paciente::class);
    }
}
