<?php

namespace App\Services\Estoque;

use App\Models\Lote;
use App\Models\Movimentacao;
use Illuminate\Support\Facades\Auth;

class MovimentacaoService
{
    /**
     * Cria uma movimentação de entrada.
     *
     * @param  array  $dados  Dados da movimentação, contendo:
     *                        - int    $produto_id
     *                        - int|null $lote_id
     *                        - int|float $quantidade
     *                        - string|null $motivo
     *                        - string|null $documento
     *                        - float|null $valor_unitario
     *                        - \DateTime|string|null $data_movimentacao
     *                        - int|null $user_id
     */
    public static function criarEntrada(array $dados): Movimentacao
    {
        return Movimentacao::create([
            'tipo' => 'entrada',
            'produto_id' => $dados['produto_id'],
            'lote_id' => $dados['lote_id'] ?? null,
            'quantidade' => $dados['quantidade'],
            'data_movimentacao' => $dados['data_movimentacao'] ?? now(),
            'motivo' => $dados['motivo'] ?? 'Entrada padrão',
            'documento' => $dados['documento'] ?? null,
            'user_id' => $dados['user_id'] ?? Auth::id(),
            'valor_unitario' => $dados['valor_unitario'] ?? 0,
            'is_manual' => $dados['is_manual'] ?? true,
        ]);
    }

    /**
     * Criar movimentação de saída.
     *
     * @param  array  $dados  Dados da movimentação, contendo:
     *                        - int    $produto_id
     *                        - int|null $lote_id
     *                        - int|float $quantidade
     *                        - string|null $motivo
     *                        - string|null $documento
     *                        - \DateTime|string|null $data_movimentacao
     *                        - int|null $user_id
     */
    public static function criarSaida(array $dados): Movimentacao
    {
        $lote = Lote::findOrFail($dados['lote_id']);

        return Movimentacao::create([
            'tipo' => 'saida',
            'produto_id' => $dados['produto_id'],
            'lote_id' => $dados['lote_id'] ?? null,
            'quantidade' => $dados['quantidade'],
            'data_movimentacao' => $dados['data_movimentacao'] ?? now(),
            'motivo' => $dados['motivo'] ?? 'Saída padrão',
            'documento' => $dados['documento'] ?? null,
            'user_id' => $dados['user_id'] ?? Auth::id(),
            'valor_unitario' => $dados['valor_unitario'] ?? $lote->valor_unitario,
            'is_manual' => $dados['is_manual'] ?? true,
        ]);
    }

    /**
     * Criar movimentação de ajuste.
     *
     * @param  array  $dados  Dados da movimentação, contendo:
     *                        - int    $produto_id
     *                        - int|null $lote_id
     *                        - int|float $quantidade
     *                        - string|null $motivo
     *                        - string|null $documento
     *                        - float|null $valor_unitario
     *                        - \DateTime|string|null $data_movimentacao
     *                        - int|null $user_id
     */
    public static function criarAjuste(array $dados): Movimentacao
    {
        $lote = Lote::findOrFail($dados['lote_id']);

        return Movimentacao::create([
            'tipo' => 'ajuste',
            'produto_id' => $dados['produto_id'],
            'lote_id' => $dados['lote_id'] ?? null,
            'quantidade' => $dados['quantidade'],
            'data_movimentacao' => $dados['data_movimentacao'] ?? now(),
            'motivo' => $dados['motivo'] ?? 'Ajuste padrão',
            'documento' => $dados['documento'] ?? null,
            'user_id' => $dados['user_id'] ?? Auth::id(),
            'valor_unitario' => $dados['valor_unitario'] ?? $lote->valor_unitario,
            'is_manual' => $dados['is_manual'] ?? true,
        ]);
    }
}
