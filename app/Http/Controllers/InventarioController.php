<?php

namespace App\Http\Controllers;

use App\Models\Inventario;
use App\Models\InventarioLote;
use Illuminate\View\View;

class InventarioController extends Controller
{
    /**
     * Exibe o relatório de conferência para impressão.
     */
    public function imprimirRelatorio(int $id): View
    {
        $inventario = Inventario::with(['user'])->findOrFail($id);

        // Carregar itens ordenados igual na View do Filament
        $itens = InventarioLote::with(['lote.produto'])
            ->join('lotes', 'inventario_lote.lote_id', '=', 'lotes.id')
            ->join('produtos', 'lotes.produto_id', '=', 'produtos.id')
            ->where('inventario_id', $id)
            ->orderBy('produtos.nome')
            ->select('inventario_lote.*')
            ->get();

        return view('inventarios.relatorio-conferencia', compact('inventario', 'itens'));
    }
}
