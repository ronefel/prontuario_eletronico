<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class Produto extends BaseModel
{
    use SoftDeletes;

    protected $table = 'produtos';

    protected $fillable = ['nome', 'descricao', 'unidade_medida', 'preco_unitario', 'estoque_minimo', 'estoque_maximo', 'categoria_id', 'fornecedor_id'];

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function fornecedor()
    {
        return $this->belongsTo(Fornecedor::class);
    }

    public function lotes()
    {
        return $this->hasMany(Lote::class);
    }

    public function movimentacoes()
    {
        return $this->hasMany(Movimentacao::class);
    }

    public function inventarios()
    {
        return $this->hasMany(Inventario::class);
    }
}
