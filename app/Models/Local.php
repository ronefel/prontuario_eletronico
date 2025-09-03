<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $nome
 * @property string|null $endereco
 * @property int|null $capacidade
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Database\Eloquent\Collection<int, \App\Models\Lote> $lotes
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Local newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Local newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Local onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Local query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Local whereCapacidade($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Local whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Local whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Local whereEndereco($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Local whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Local whereNome($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Local whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Local withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Local withoutTrashed()
 * @mixin \Eloquent
 */
class Local extends BaseModel
{
    use SoftDeletes;

    protected $table = 'locais';

    protected $fillable = ['nome', 'endereco', 'capacidade'];

    public function lotes()
    {
        return $this->hasMany(Lote::class);
    }
}
