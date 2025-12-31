<?php

namespace App\Filament\Resources\LoteResource\Pages;

use App\Filament\Resources\LoteResource;
use App\Models\Lote;
use App\Models\Produto;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListLotes extends ListRecords
{
    protected static string $resource = LoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function getListeners(): array
    {
        return [
            'updateProductPrice' => 'handleUpdateProductPrice',
        ];
    }

    public function handleUpdateProductPrice(int|string $loteId): void
    {
        $lote = Lote::find($loteId);

        if (! $lote instanceof Lote) {
            return;
        }

        $produto = $lote->produto;

        if ($produto instanceof Produto) {
            $produto->update([
                'valor_unitario_referencia' => $lote->valor_unitario,
            ]);

            Notification::make()
                ->success()
                ->title('Produto atualizado')
                ->body('O valor de referência do produto foi atualizado com sucesso.')
                ->send();
        }
    }
}
