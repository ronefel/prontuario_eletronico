<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class Lote extends BaseModel
{
    use SoftDeletes;

    protected $table = 'lotes';

    protected $fillable = ['produto_id', 'numero_lote', 'data_fabricacao', 'data_validade', 'quantidade_inicial', 'quantidade_atual', 'local_id', 'status'];

    protected $dates = ['data_fabricacao', 'data_validade'];

    public function produto()
    {
        return $this->belongsTo(Produto::class);
    }

    public function local()
    {
        return $this->belongsTo(Local::class);
    }

    public function movimentacoes()
    {
        return $this->hasMany(Movimentacao::class);
    }
}
