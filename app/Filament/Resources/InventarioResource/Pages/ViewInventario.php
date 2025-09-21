<?php

namespace App\Filament\Resources\InventarioResource\Pages;

use App\Filament\Resources\InventarioResource;
use App\Models\Inventario;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewInventario extends ViewRecord
{
    protected static string $resource = InventarioResource::class;

    public ?Inventario $proximo;

    public ?Inventario $anterior;

    public function getTitle(): string
    {
        return 'Inventário #'.$this->record->id;
    }

    /**
     * Busca o registro anterior e o próximo
     */
    protected function montarNavegacao(): void
    {
        $this->proximo = Inventario::where('id', '>', $this->record->id)
            ->orderBy('id')
            ->first();

        $this->anterior = Inventario::where('id', '<', $this->record->id)
            ->orderBy('id', 'desc')
            ->first();
    }

    /**
     * Verifica se existe próximo registro
     */
    protected function temProximo(): bool
    {
        return $this->proximo !== null;
    }

    /**
     * Verifica se existe registro anterior
     */
    protected function temAnterior(): bool
    {
        return $this->anterior !== null;
    }

    protected function getHeaderActions(): array
    {
        $this->montarNavegacao();

        return [
            // Botão Voltar (mantém o botão original)
            Actions\Action::make('voltar')
                ->label('Voltar')
                ->icon('heroicon-o-arrow-left')
                ->size('sm')
                ->url(static::getResource()::getUrl('index'))
                ->tooltip('Voltar para a lista de inventários'),

            // Botão Anterior
            Actions\Action::make('anterior')
                ->label('')
                ->outlined()
                ->icon('heroicon-o-arrow-left')
                ->size('sm')
                ->disabled(! $this->temAnterior())
                ->url(fn () => $this->temAnterior()
                    ? static::getResource()::getUrl('view', ['record' => $this->anterior])
                    : null
                )
                ->tooltip(fn () => $this->temAnterior()
                    ? 'Inventário #'.$this->anterior->id
                    : 'Nenhum inventário anterior'
                ),

            // Botão Próximo
            Actions\Action::make('proximo')
                ->label('')
                ->outlined()
                ->icon('heroicon-o-arrow-right')
                ->size('sm')
                ->disabled(! $this->temProximo())
                ->url(fn () => $this->temProximo()
                    ? static::getResource()::getUrl('view', ['record' => $this->proximo])
                    : null
                )
                ->tooltip(fn () => $this->temProximo()
                    ? 'Inventário #'.$this->proximo->id
                    : 'Nenhum inventário posterior'
                ),
        ];
    }
}
