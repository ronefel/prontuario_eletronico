<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $nome
 * @property string|null $nota
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $ordem
 * @property Collection<int, Testador> $testadores
 * @method static \Database\Factories\CategoriaTestadorFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoriaTestador newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoriaTestador newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoriaTestador query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoriaTestador whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoriaTestador whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoriaTestador whereNome($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoriaTestador whereNota($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoriaTestador whereOrdem($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoriaTestador whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CategoriaTestador extends BaseModel
{
    use HasFactory;

    protected $table = 'categorias_testadores';

    public function testadores(): HasMany
    {
        return $this->hasMany(Testador::class);
    }
}
