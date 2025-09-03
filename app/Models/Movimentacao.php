<?php

namespace App\Models;

use App\Casts\DatetimeWithTimezone;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

/**
 * @property int $id
 * @property string $tipo
 * @property int $produto_id
 * @property int $lote_id
 * @property int $quantidade
 * @property mixed $data_movimentacao
 * @property string|null $motivo
 * @property int $user_id
 * @property string|null $documento
 * @property string|null $valor_unitario
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\Lote $lote
 * @property-read \App\Models\Produto $produto
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Movimentacao newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Movimentacao newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Movimentacao onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Movimentacao query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Movimentacao whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Movimentacao whereDataMovimentacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Movimentacao whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Movimentacao whereDocumento($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Movimentacao whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Movimentacao whereLoteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Movimentacao whereMotivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Movimentacao whereProdutoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Movimentacao whereQuantidade($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Movimentacao whereTipo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Movimentacao whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Movimentacao whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Movimentacao whereValorUnitario($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Movimentacao withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Movimentacao withoutTrashed()
 * @mixin \Eloquent
 */
class Movimentacao extends BaseModel
{
    use SoftDeletes;

    protected $table = 'movimentacoes';

    protected $casts = [
        'data_movimentacao' => DatetimeWithTimezone::class,
    ];

    protected static function booted()
    {
        static::creating(function ($movimentacao) {
            $movimentacao->user_id = Auth::id();

            if ($movimentacao->tipo === 'saida' && $movimentacao->quantidade > 0) {
                $movimentacao->quantidade *= -1;
            }
        });

        static::updating(function ($movimentacao) {
            if ($movimentacao->tipo === 'saida' && $movimentacao->quantidade > 0) {
                $movimentacao->quantidade *= -1;
            }
        });
    }

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
