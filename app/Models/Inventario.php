<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class Inventario extends BaseModel
{
    use SoftDeletes;

    protected $table = 'inventarios';

    protected $fillable = ['data_inventario', 'tipo', 'produto_id', 'lote_id', 'quantidade_contada', 'quantidade_registrada', 'motivo_discrepancia', 'user_id', 'status'];

    protected $dates = ['data_inventario'];

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
