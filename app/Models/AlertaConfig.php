<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class AlertaConfig extends BaseModel
{
    use SoftDeletes;

    protected $fillable = ['tipo', 'produto_id', 'threshold', 'metodo_notificacao', 'usuarios_notificados'];

    protected $casts = ['usuarios_notificados' => 'array'];

    public function produto()
    {
        return $this->belongsTo(Produto::class);
    }
}
