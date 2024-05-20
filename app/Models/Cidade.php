<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class Cidade extends Model
{
    use HasFactory;

    public function pacientes()
    {
        return $this->hasMany(Paciente::class);
    }
}
