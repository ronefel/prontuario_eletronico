<?php

namespace App\Filament\Resources\Lotes\Pages;

use App\Filament\Resources\Lotes\LoteResource;
use App\Models\Lote;
use App\Models\Produto;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateLote extends CreateRecord
{
    protected static string $resource = LoteResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->previousUrl ?? $this->getResource()::getUrl('index');
    }

    protected function afterCreate(): void
    {
        /** @var Lote $lote */
        $lote = $this->record;

        /** @var Produto $produto */
        $produto = $lote->produto;

        if ((float) $lote->valor_unitario !== (float) $produto->valor_unitario_referencia) {
            Notification::make()
                ->warning()
                ->title('Preço de referência divergente')
                ->body('O valor unitário do lote (R$ '.number_format((float) $lote->valor_unitario, 2, ',', '.').') é diferente do valor de referência do produto (R$ '.number_format((float) $produto->valor_unitario_referencia, 2, ',', '.').'). Deseja atualizar o valor de referência do produto?')
                ->actions([
                    Action::make('atualizar')
                        ->label('Sim, atualizar')
                        ->button()
                        ->color('warning')
                        ->dispatch('updateProductPrice', ['loteId' => $lote->id])
                        ->close(),
                ])
                ->seconds(10)
                ->send();
        }
    }

    /**
     * @return array<string, string>
     */
    protected function getListeners(): array
    {
        $listeners = parent::getListeners();
        $listeners['updateProductPrice'] = 'handleUpdateProductPrice';

        return $listeners;
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
