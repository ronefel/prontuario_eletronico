<?php

namespace App\Models;

use App\Casts\DatetimeWithTimezone;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lote extends BaseModel
{
    use SoftDeletes;

    protected $table = 'lotes';

    protected $fillable = ['produto_id', 'numero_lote', 'data_fabricacao', 'data_validade', 'quantidade_inicial', 'quantidade_atual', 'local_id', 'status'];

    protected $dates = ['data_fabricacao', 'data_validade'];

    protected $casts = [
        'data_fabricacao' => DatetimeWithTimezone::class,
        'data_validade' => DatetimeWithTimezone::class,
    ];

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
