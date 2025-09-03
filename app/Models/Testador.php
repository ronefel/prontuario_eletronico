<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $id
 * @property string $numero
 * @property string $nome
 * @property int $categoria_testador_id
 * @property bool $ativo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \App\Models\CategoriaTestador $categoria
 * @method static \Database\Factories\TestadorFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Testador newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Testador newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Testador query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Testador whereAtivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Testador whereCategoriaTestadorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Testador whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Testador whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Testador whereNome($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Testador whereNumero($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Testador whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Testador extends BaseModel
{
    use HasFactory;

    protected $table = 'testadores';

    protected $casts = [
        'ativo' => 'boolean',
    ];

    public function categoria()
    {
        return $this->belongsTo(CategoriaTestador::class, 'categoria_testador_id', 'id', 'categorias_testadores');
    }
}
