<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $data_inventario
 * @property string $tipo
 * @property int|null $produto_id
 * @property int|null $lote_id
 * @property int $quantidade_contada
 * @property int $quantidade_registrada
 * @property int $discrepancia
 * @property string|null $motivo_discrepancia
 * @property int $user_id
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\Lote|null $lote
 * @property-read \App\Models\Produto|null $produto
 * @property-read \App\Models\User $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inventario newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inventario newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inventario onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inventario query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inventario whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inventario whereDataInventario($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inventario whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inventario whereDiscrepancia($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inventario whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inventario whereLoteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inventario whereMotivoDiscrepancia($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inventario whereProdutoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inventario whereQuantidadeContada($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inventario whereQuantidadeRegistrada($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inventario whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inventario whereTipo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inventario whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inventario whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inventario withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inventario withoutTrashed()
 *
 * @mixin \Eloquent
 */
class Inventario extends BaseModel
{
    use SoftDeletes;

    protected $table = 'inventarios';

    protected $fillable = ['data_inventario', 'tipo', 'produto_id', 'lote_id', 'quantidade_contada', 'quantidade_registrada', 'motivo_discrepancia', 'user_id', 'status'];

    protected $dates = ['data_inventario'];

    public function produto()
    {
        return $this->belongsTo(Produto::class);
    }

    public function lote()
    {
        return $this->belongsTo(Lote::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
