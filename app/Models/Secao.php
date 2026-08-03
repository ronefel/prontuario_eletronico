<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $nome
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Collection<int, Campo> $campos
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Secao newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Secao newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Secao query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Secao whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Secao whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Secao whereNome($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Secao whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Secao extends BaseModel
{
    use HasFactory;

    protected $table = 'secoes';

    public function campos()
    {
        return $this->hasMany(Campo::class);
    }
}
