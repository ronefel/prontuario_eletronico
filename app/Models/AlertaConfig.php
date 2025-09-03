<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $tipo
 * @property int|null $produto_id
 * @property int $threshold
 * @property string $metodo_notificacao
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\Produto|null $produto
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AlertaConfig newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AlertaConfig newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AlertaConfig onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AlertaConfig query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AlertaConfig whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AlertaConfig whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AlertaConfig whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AlertaConfig whereMetodoNotificacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AlertaConfig whereProdutoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AlertaConfig whereThreshold($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AlertaConfig whereTipo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AlertaConfig whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AlertaConfig withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AlertaConfig withoutTrashed()
 * @mixin \Eloquent
 */
class AlertaConfig extends BaseModel
{
    use SoftDeletes;

    protected $table = 'alertas_config';

    protected $fillable = ['tipo', 'produto_id', 'threshold', 'metodo_notificacao'];

    public function produto()
    {
        return $this->belongsTo(Produto::class);
    }
}
