<?php

namespace App\Models;

use App\Casts\DatetimeWithTimezone;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $tratamento_id
 * @property mixed $data_aplicacao
 * @property string|null $observacoes
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Database\Eloquent\Collection<int, \App\Models\AplicacaoLote> $itens
 * @property \App\Models\AplicacaoLote|null $pivot
 * @property \Illuminate\Database\Eloquent\Collection<int, \App\Models\Lote> $lotes
 * @property \App\Models\Tratamento $tratamento
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Aplicacao newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Aplicacao newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Aplicacao onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Aplicacao query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Aplicacao whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Aplicacao whereDataAplicacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Aplicacao whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Aplicacao whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Aplicacao whereObservacoes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Aplicacao whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Aplicacao whereTratamentoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Aplicacao whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Aplicacao withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Aplicacao withoutTrashed()
 * @mixin \Eloquent
 */
class Aplicacao extends BaseModel
{
    use SoftDeletes;

    protected $table = 'aplicacoes';

    protected $casts = [
        'data_aplicacao' => DatetimeWithTimezone::class,
    ];

    public function tratamento()
    {
        return $this->belongsTo(Tratamento::class);
    }

    public function itens()
    {
        return $this->hasMany(AplicacaoLote::class);
    }

    public function lotes()
    {
        return $this->belongsToMany(Lote::class, 'aplicacao_lote')
            ->using(AplicacaoLote::class)
            ->withPivot('quantidade')
            ->withTimestamps();
    }

    public function getValorTotalAttribute(): float
    {
        return (float) $this->itens->sum('valor_total');
    }
}
