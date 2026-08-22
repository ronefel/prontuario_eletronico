<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class ExameResultadoItem extends BaseModel
{
    use HasFactory;

    protected $table = 'exame_resultado_itens';

    protected $fillable = [
        'exame_registro_id',
        'exame_parametro_id',
        'valor_resultado',
    ];

    protected $casts = [
        'valor_resultado' => 'float',
    ];

    public function registro()
    {
        return $this->belongsTo(ExameRegistro::class, 'exame_registro_id');
    }

    public function parametro()
    {
        return $this->belongsTo(ExameParametro::class, 'exame_parametro_id');
    }

    public function getStatusNormalidadeAttribute(): string
    {
        $parametro = $this->parametro;
        if (! $parametro) {
            return 'informativo';
        }

        $min = $parametro->valor_minimo_ideal;
        $max = $parametro->valor_maximo_ideal;

        if ($min === null && $max === null) {
            return 'informativo';
        }

        $valor = (float) $this->valor_resultado;

        if ($min !== null && $valor < $min) {
            return 'baixo';
        }

        if ($max !== null && $valor > $max) {
            return 'alto';
        }

        return 'normal';
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status_normalidade) {
            'baixo' => 'Baixo',
            'alto' => 'Alto',
            'normal' => 'Normal',
            default => 'Informativo',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status_normalidade) {
            'baixo' => 'warning',
            'alto' => 'danger',
            'normal' => 'success',
            default => 'info',
        };
    }
}
