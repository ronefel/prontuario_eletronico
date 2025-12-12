<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property \Illuminate\Database\Eloquent\Collection<int, \App\Models\Aplicacao> $aplicacoes
 * @property \App\Models\Paciente|null $paciente
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tratamento newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tratamento newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tratamento query()
 * @mixin \Eloquent
 */
class Tratamento extends BaseModel
{
    use SoftDeletes;

    protected $table = 'tratamentos';

    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }

    public function aplicacoes()
    {
        return $this->hasMany(Aplicacao::class);
    }

    public function getStatusAttribute(): string
    {
        if ($this->trashed()) {
            return 'cancelado';
        }

        $aplicacoes = $this->aplicacoes;

        if ($aplicacoes->isEmpty()) {
            return 'planejado';
        }

        $hasAplicada = $aplicacoes->contains('status', 'aplicada');

        if (! $hasAplicada) {
            return 'planejado';
        }

        $allAplicada = $aplicacoes->every(fn ($app) => $app->status === 'aplicada');

        if ($allAplicada) {
            return 'concluido';
        }

        return 'em_andamento';
    }
}
