<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @property int $id
 * @property int $inventario_id
 * @property int $lote_id
 * @property int $quantidade_contada
 * @property int $quantidade_registrada
 * @property int $discrepancia
 * @property string|null $motivo_discrepancia
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \App\Models\Inventario $inventario
 * @property \App\Models\Lote $lote
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventarioLote newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventarioLote newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventarioLote query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventarioLote whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventarioLote whereDiscrepancia($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventarioLote whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventarioLote whereInventarioId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventarioLote whereLoteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventarioLote whereMotivoDiscrepancia($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventarioLote whereQuantidadeContada($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventarioLote whereQuantidadeRegistrada($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventarioLote whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class InventarioLote extends Pivot
{
    public $incrementing = true;

    protected static function booted()
    {
        static::creating(function ($inventarioLote) {
            if ($inventarioLote->quantidade_contada == $inventarioLote->quantidade_registrada) {
                $inventarioLote->motivo_discrepancia = null;
            }
        });

        static::updating(function ($inventarioLote) {
            if ($inventarioLote->quantidade_contada == $inventarioLote->quantidade_registrada) {
                $inventarioLote->motivo_discrepancia = null;
            }
        });
    }

    public function inventario(): BelongsTo
    {
        return $this->belongsTo(Inventario::class);
    }

    public function lote(): BelongsTo
    {
        return $this->belongsTo(Lote::class);
    }
}
