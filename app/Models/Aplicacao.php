<?php

namespace App\Models;

use App\Casts\DatetimeWithTimezone;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property \App\Models\Lote|null $lote
 * @property \App\Models\Produto|null $produto
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

    public function lote()
    {
        return $this->belongsTo(Lote::class);
    }
}
