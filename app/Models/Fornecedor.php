<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $nome
 * @property string|null $email
 * @property string|null $telefone
 * @property string|null $endereco
 * @property int $prazo_entrega
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @propert \Illuminate\Database\Eloquent\Collection<int, \App\Models\Produto> $produtos
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Fornecedor newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Fornecedor newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Fornecedor onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Fornecedor query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Fornecedor whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Fornecedor whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Fornecedor whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Fornecedor whereEndereco($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Fornecedor whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Fornecedor whereNome($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Fornecedor wherePrazoEntrega($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Fornecedor whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Fornecedor whereTelefone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Fornecedor whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Fornecedor withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Fornecedor withoutTrashed()
 * @mixin \Eloquent
 */
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
