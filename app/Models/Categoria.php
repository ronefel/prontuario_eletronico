<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $nome
 * @property string|null $descricao
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Produto> $produtos
 * @property-read int|null $produtos_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Categoria newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Categoria newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Categoria onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Categoria query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Categoria whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Categoria whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Categoria whereDescricao($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Categoria whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Categoria whereNome($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Categoria whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Categoria withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Categoria withoutTrashed()
 * @mixin \Eloquent
 */
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
