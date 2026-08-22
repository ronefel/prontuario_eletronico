<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class ExameParametro extends BaseModel
{
    use HasFactory;

    protected $table = 'exame_parametros';

    protected $fillable = [
        'exame_id',
        'nome_parametro',
        'unidade_medida',
        'valor_minimo_ideal',
        'valor_maximo_ideal',
    ];

    protected $casts = [
        'valor_minimo_ideal' => 'float',
        'valor_maximo_ideal' => 'float',
    ];

    public function exame()
    {
        return $this->belongsTo(ExameLaboratorial::class, 'exame_id');
    }

    public function resultadoItens()
    {
        return $this->hasMany(ExameResultadoItem::class, 'exame_parametro_id');
    }
}
