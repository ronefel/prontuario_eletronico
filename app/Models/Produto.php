<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $nome
 * @property string|null $descricao
 * @property string $unidade_medida
 * @property string|null $valor_unitario_referencia
 * @property int $estoque_minimo
 * @property int $estoque_maximo
 * @property int $categoria_id
 * @property int|null $fornecedor_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\Categoria $categoria
 * @property-read \App\Models\Fornecedor|null $fornecedor
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Inventario> $inventarios
 * @property-read int|null $inventarios_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Lote> $lotes
 * @property-read int|null $lotes_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Movimentacao> $movimentacoes
 * @property-read int|null $movimentacoes_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Produto newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Produto newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Produto onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Produto query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Produto whereCategoriaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Produto whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Produto whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Produto whereDescricao($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Produto whereEstoqueMaximo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Produto whereEstoqueMinimo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Produto whereFornecedorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Produto whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Produto whereNome($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Produto whereUnidadeMedida($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Produto whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Produto whereValorUnitarioReferencia($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Produto withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Produto withoutTrashed()
 * @mixin \Eloquent
 */
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
