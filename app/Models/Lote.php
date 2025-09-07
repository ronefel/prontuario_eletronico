<?php

namespace App\Models;

use App\Casts\DatetimeWithTimezone;
use App\Services\Estoque\MovimentacaoService;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

/**
 * @property int $id
 * @property int $produto_id
 * @property int|null $fornecedor_id
 * @property string $numero_lote
 * @property mixed|null $data_fabricacao
 * @property mixed|null $data_validade
 * @property int $quantidade_inicial
 * @property string|null $documento
 * @property string|null $valor_unitario
 * @property int $local_id
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \App\Models\Fornecedor|null $fornecedor
 * @property string $display_name
 * @property mixed $quantidade_atual
 * @property \App\Models\Local $local
 * @property \Illuminate\Database\Eloquent\Collection<int, \App\Models\Movimentacao> $movimentacoes
 * @property \App\Models\Produto $produto
 * @property-read \Illuminate\Database\Eloquent\Relations\Pivot|\stdClass $pivot
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lote newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lote newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lote onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lote query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lote whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lote whereDataFabricacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lote whereDataValidade($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lote whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lote whereDocumento($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lote whereFornecedorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lote whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lote whereLocalId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lote whereNumeroLote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lote whereProdutoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lote whereQuantidadeInicial($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lote whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lote whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lote whereValorUnitario($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lote withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lote withoutTrashed()
 * @mixin \Eloquent
 */
class Lote extends BaseModel
{
    use SoftDeletes;

    protected $table = 'lotes';

    protected $casts = [
        'data_fabricacao' => DatetimeWithTimezone::class,
        'data_validade' => DatetimeWithTimezone::class,
    ];

    protected static function booted()
    {
        // Depois de criado o lote, cria movimentação se quantidade inicial > 0
        static::created(function (Lote $lote) {
            if ($lote->quantidade_inicial > 0) {
                MovimentacaoService::criarEntrada([
                    'produto_id' => $lote->produto_id,
                    'lote_id' => $lote->id,
                    'quantidade' => $lote->quantidade_inicial,
                    'data_movimentacao' => now(),
                    'motivo' => 'Cadastro inicial do lote #'.$lote->numero_lote,
                    'documento' => $lote->documento_movimentacao_temp ?? null,
                    'user_id' => Auth::id(),
                    'valor_unitario' => $lote->valor_unitario_movimentacao_temp ?? $lote->produto->valor_unitario_referencia,
                ]);
            }
        });

        // Antes de atualizar, remove campos que não pertencem ao Lote
        static::updating(function ($lote) {
            unset($lote->documento_movimentacao);
            unset($lote->valor_unitario_movimentacao);
        });

        // Depois de atualizado o lote, cria movimentação se quantidade inicial foi alterada
        static::updated(function ($lote) {
            $quantidade_velha = $lote->getOriginal('quantidade_inicial');
            $quantidade_nova = $lote->quantidade_inicial;

            if ($quantidade_velha != $quantidade_nova) {
                $diferenca = $quantidade_nova - $quantidade_velha;

                MovimentacaoService::criarAjuste([
                    'tipo' => 'ajuste',
                    'produto_id' => $lote->produto_id,
                    'lote_id' => $lote->id,
                    'quantidade' => $diferenca,
                    'data_movimentacao' => now(),
                    'motivo' => 'Ajuste na quantidade inicial do lote #'.$lote->numero_lote,
                    'documento' => null,
                    'user_id' => Auth::id(),
                    'valor_unitario' => $lote->produto->preco_unitario,
                ]);
            }
        });

        // Depois de deletado o lote, cria movimentação se quantidade atual > 0
        static::deleted(function ($lote) {
            $quantidade_atual = $lote->quantidade_atual;

            if ($quantidade_atual > 0) {
                MovimentacaoService::criarSaida([
                    'produto_id' => $lote->produto_id,
                    'lote_id' => $lote->id,
                    'quantidade' => -$quantidade_atual,
                    'data_movimentacao' => now(),
                    'motivo' => 'Exclusão do lote #'.$lote->numero_lote,
                    'user_id' => Auth::id(),
                ]);
            }
        });
    }

    public function produto()
    {
        return $this->belongsTo(Produto::class);
    }

    public function fornecedor()
    {
        return $this->belongsTo(Fornecedor::class);
    }

    public function local()
    {
        return $this->belongsTo(Local::class);
    }

    public function movimentacoes()
    {
        return $this->hasMany(Movimentacao::class);
    }

    public function getQuantidadeAtualAttribute()
    {
        return $this->movimentacoes()->sum('quantidade');
    }

    public function getDisplayNameAttribute(): string
    {
        $numero = "<span class='font-semibold'>{$this->numero_lote}</span>";
        $dados = "<span class='text-gray-400'> (Venc. "
            .($this->data_validade?->format('d/m/y') ?? '-')
            ." | {$this->produto->nome})</span>";

        return $numero.$dados;
    }
}
