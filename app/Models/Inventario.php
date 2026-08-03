<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $data_inventario
 * @property string $tipo
 * @property int $user_id
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property Collection<int, Lote> $lotes
 * @property Collection<int, Produto> $produtos
 * @property User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inventario newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inventario newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inventario onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inventario query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inventario whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inventario whereDataInventario($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inventario whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inventario whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inventario whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inventario whereTipo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inventario whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inventario whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inventario withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inventario withoutTrashed()
 * @mixin \Eloquent
 */
class Inventario extends BaseModel
{
    use SoftDeletes;

    protected $table = 'inventarios';

    protected $fillable = ['data_inventario', 'tipo', 'user_id', 'status'];

    protected $casts = [
        'data_inventario' => 'date:Y-m-d',
    ];

    protected static function booted()
    {
        static::creating(function ($inventario) {
            unset($inventario->local_id);
            unset($inventario->produto_id);
        });

        static::updating(function ($inventario) {
            unset($inventario->local_id);
            unset($inventario->produto_id);
        });
    }

    public function lotes()
    {
        return $this->belongsToMany(Lote::class, 'inventario_lote', 'inventario_id', 'lote_id')
            ->withPivot('quantidade_contada', 'quantidade_registrada', 'discrepancia', 'motivo_discrepancia')
            ->withTimestamps();
    }

    public function inventarioLotes(): HasMany
    {
        return $this->hasMany(InventarioLote::class);
    }

    public function produtos()
    {
        return $this->hasManyThrough(
            Produto::class,
            Lote::class,
            'id', // Chave estrangeira em lotes
            'id', // Chave primária em produtos
            'id', // Chave primária em inventario
            'produto_id' // Chave estrangeira em lotes que aponta para produtos
        )->distinct();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
