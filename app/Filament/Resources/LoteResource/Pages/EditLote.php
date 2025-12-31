<?php

namespace App\Filament\Resources\LoteResource\Pages;

use App\Filament\Resources\LoteResource;
use App\Models\Lote;
use App\Models\Produto;
use Filament\Actions;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

/**
 * @property-read Lote $record
 */
class EditLote extends EditRecord
{
    protected static string $resource = LoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->previousUrl ?? $this->getResource()::getUrl('index');
    }

    protected function afterSave(): void
    {
        $lote = $this->record;
        $produto = $lote->produto;

        if ($produto instanceof Produto && (float) $lote->valor_unitario !== (float) $produto->valor_unitario_referencia) {
            Notification::make()
                ->warning()
                ->title('Preço de referência divergente')
                ->body('O valor unitário do lote (R$ '.number_format((float) $lote->valor_unitario, 2, ',', '.').') é diferente do valor de referência do produto (R$ '.number_format((float) $produto->valor_unitario_referencia, 2, ',', '.').'). Deseja atualizar o valor de referência do produto?')
                ->actions([
                    Action::make('atualizar')
                        ->label('Sim, atualizar valor de referência')
                        ->button()
                        ->color('warning')
                        ->dispatch('updateProductPrice', ['loteId' => $lote->id])
                        ->close(),
                ])
                ->seconds(15)
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
