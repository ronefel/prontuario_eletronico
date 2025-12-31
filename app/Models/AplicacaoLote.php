<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @property int $id
 * @property int $aplicacao_id
 * @property int $lote_id
 * @property int $quantidade
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \App\Models\Aplicacao $aplicacao
 * @property \App\Models\Lote $lote
 * @property float $valor_total
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AplicacaoLote newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AplicacaoLote newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AplicacaoLote query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AplicacaoLote whereAplicacaoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AplicacaoLote whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AplicacaoLote whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AplicacaoLote whereLoteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AplicacaoLote whereQuantidade($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AplicacaoLote whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class AplicacaoLote extends Pivot
{
    protected $table = 'aplicacao_lote';

    protected $fillable = [
        'aplicacao_id',
        'lote_id',
        'quantidade',
    ];

    public function aplicacao()
    {
        return $this->belongsTo(Aplicacao::class);
    }

    public function lote()
    {
        return $this->belongsTo(Lote::class);
    }

    public function getValorTotalAttribute(): float
    {
        return (float) ($this->quantidade * ($this->lote->valor_unitario ?? 0));
    }
}
