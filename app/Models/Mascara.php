<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $id
 * @property string $nome
 * @property string $descricao
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mascara newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mascara newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mascara query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mascara whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mascara whereDescricao($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mascara whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mascara whereNome($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mascara whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Mascara extends BaseModel
{
    use HasFactory;

    protected $fillable = ['nome', 'descricao'];
}
