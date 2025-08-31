<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class Movimentacao extends BaseModel
{
    use SoftDeletes;

    protected $table = 'movimentacoes';

    protected $fillable = ['tipo', 'produto_id', 'lote_id', 'quantidade', 'data_movimentacao', 'motivo', 'user_id', 'documento', 'valor_unitario'];

    protected $dates = ['data_movimentacao'];

    public function produto()
    {
        return $this->belongsTo(Produto::class);
    }

    public function lote()
    {
        return $this->belongsTo(Lote::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
