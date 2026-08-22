<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class ExameLaboratorial extends BaseModel
{
    use HasFactory;

    protected $table = 'exames_laboratoriais';

    protected $fillable = [
        'nome',
        'descricao',
        'ativo',
    ];

    protected $casts = [
        'ativo' => 'boolean',
    ];

    public function parametros()
    {
        return $this->hasMany(ExameParametro::class, 'exame_id');
    }

    public function registros()
    {
        return $this->hasMany(ExameRegistro::class, 'exame_id');
    }
}
