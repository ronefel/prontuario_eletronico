<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $nome
 * @property string|null $descricao
 * @property bool $ativo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Database\Eloquent\Collection<int, \App\Models\KitItem> $itens
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kit newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kit newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kit onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kit query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kit whereAtivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kit whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kit whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kit whereDescricao($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kit whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kit whereNome($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kit whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kit withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kit withoutTrashed()
 * @mixin \Eloquent
 */
class Kit extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'ativo' => 'boolean',
    ];

    public function itens()
    {
        return $this->hasMany(KitItem::class);
    }
}
