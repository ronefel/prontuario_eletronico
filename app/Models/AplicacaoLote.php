<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

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
}
