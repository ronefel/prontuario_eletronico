<?php

namespace App\Models;

use App\Casts\DatetimeWithTimezone;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property \Illuminate\Database\Eloquent\Collection|\App\Models\AplicacaoLote[] $itens
 * @property \Illuminate\Database\Eloquent\Collection|\App\Models\Lote[] $lotes
 * @property \App\Models\Tratamento|null $tratamento
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Aplicacao newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Aplicacao newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Aplicacao query()
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
}
