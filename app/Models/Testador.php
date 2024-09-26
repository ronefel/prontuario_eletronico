<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Testador extends BaseModel
{
    use HasFactory;

    protected $table = 'testadores';

    protected $casts = [
        'ativo' => 'boolean'
    ];

    public function categoria()
    {
        return $this->belongsTo(CategoriaTestador::class, 'categoria_testador_id', 'id', 'categorias_testadores');
    }
}
