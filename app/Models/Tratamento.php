<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $paciente_id
 * @property string $nome
 * @property string|null $observacao
 * @property \Illuminate\Support\Carbon $data_inicio
 * @property \Illuminate\Support\Carbon|null $data_fim
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property numeric|null $valor_cobrado
 * @property \Illuminate\Database\Eloquent\Collection<int, \App\Models\Aplicacao> $aplicacoes
 * @property float $custo_total
 * @property string $progresso
 * @property float $saldo
 * @property \App\Models\Paciente $paciente
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tratamento newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tratamento newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tratamento onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tratamento query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tratamento whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tratamento whereDataFim($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tratamento whereDataInicio($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tratamento whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tratamento whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tratamento whereNome($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tratamento whereObservacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tratamento wherePacienteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tratamento whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tratamento whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tratamento whereValorCobrado($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tratamento withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tratamento withoutTrashed()
 * @mixin \Eloquent
 */
class Tratamento extends BaseModel
{
    use SoftDeletes;

    protected $table = 'tratamentos';

    protected $guarded = [];

    protected $casts = [
        'valor_cobrado' => 'decimal:2',
        'data_inicio' => 'date:Y-m-d',
        'data_fim' => 'date:Y-m-d',
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

    public function getProgressoAttribute(): string
    {
        $aplicacoes = $this->aplicacoes;

        $total = $aplicacoes->count();
        $aplicadas = $aplicacoes->where('status', 'aplicada')->count();

        return "{$aplicadas}/{$total}";
    }

    public function getStatusAttribute(): string
    {
        if ($this->trashed()) {
            return 'excluido';
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
