<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class ExameRegistro extends BaseModel
{
    use HasFactory;

    protected $table = 'exame_registros';

    protected $fillable = [
        'paciente_id',
        'exame_id',
        'data_exame',
        'observacoes',
    ];

    protected $casts = [
        'data_exame' => 'date',
    ];

    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }

    public function exame()
    {
        return $this->belongsTo(ExameLaboratorial::class, 'exame_id');
    }

    public function itens()
    {
        return $this->hasMany(ExameResultadoItem::class, 'exame_registro_id');
    }
}
