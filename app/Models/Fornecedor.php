<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class Fornecedor extends BaseModel
{
    use SoftDeletes;

    protected $table = 'fornecedores';

    protected $fillable = ['nome', 'email', 'telefone', 'endereco', 'prazo_entrega', 'status'];

    public function produtos()
    {
        return $this->hasMany(Produto::class);
    }
}
