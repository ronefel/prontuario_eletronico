<?php

namespace App\Models;

class Aplicacao extends BaseModel
{
    public function tratamento()
    {
        return $this->belongsTo(Tratamento::class);
    }

    public function lote()
    {
        return $this->belongsTo(Lote::class);
    }

    public function produto()
    {
        return $this->belongsTo(Produto::class);
    }
}
