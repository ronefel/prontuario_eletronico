<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class Categoria extends BaseModel
{
    use SoftDeletes;

    protected $table = 'categorias';

    protected $fillable = ['nome', 'descricao'];

    public function produtos()
    {
        return $this->hasMany(Produto::class);
    }
}
