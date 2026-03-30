<?php

namespace App\Filament\Resources\Inventarios\Pages;

use App\Filament\Resources\Inventarios\InventarioResource;
use App\Models\Inventario;
use App\Models\Lote;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListInventarios extends ListRecords
{
    protected static string $resource = InventarioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->after(function (Inventario $record, array $data) {
                    $query = Lote::where('status', 'ativo');

                    if ($data['tipo'] === 'por_local' && isset($data['local_id'])) {
                        $query->whereIn('local_id', (array) $data['local_id']);
                    } elseif ($data['tipo'] === 'por_produto' && isset($data['produto_id'])) {
                        $query->whereIn('produto_id', (array) $data['produto_id']);
                    }

                    $lotes = $query->withSum('movimentacoes', 'quantidade')->get();

                    foreach ($lotes as $lote) {
                        $record->inventarioLotes()->create([
                            'lote_id' => $lote->id,
                            'quantidade_registrada' => $lote->movimentacoes_sum_quantidade ?? 0,
                            'quantidade_contada' => $lote->movimentacoes_sum_quantidade ?? 0,
                        ]);
                    }
                }),
        ];
    }
}
