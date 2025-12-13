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

    protected $guarded = [];

    protected $casts = [
        'valor_cobrado' => 'decimal:2',
    ];

    public function getCustoTotalAttribute(): float
    {
        $custo = 0;
        foreach ($this->aplicacoes as $aplicacao) {
            foreach ($aplicacao->lotes as $lote) {
                $quantidade = $lote->pivot->quantidade ?? 0;
                $valorUnitario = $lote->valor_unitario ?? 0;
                $custo += $quantidade * $valorUnitario;
            }
        }

        return round($custo, 2);
    }

    public function getSaldoAttribute(): float
    {
        $cobrado = $this->valor_cobrado ?? 0;

        return round($cobrado - $this->custo_total, 2);
    }

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
