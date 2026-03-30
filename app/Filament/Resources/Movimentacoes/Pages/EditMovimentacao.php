<?php

namespace App\Filament\Resources\Movimentacoes\Pages;

use App\Filament\Resources\Movimentacoes\MovimentacaoResource;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditMovimentacao extends EditRecord
{
    protected static string $resource = MovimentacaoResource::class;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        if (! $this->record->is_manual) {
            Notification::make()
                ->warning()
                ->title('Acesso negado')
                ->body('Esta movimentação é automática e não pode ser editada.')
                ->send();

            $this->redirect($this->getResource()::getUrl('index'));
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->visible(fn () => $this->record->is_manual),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->previousUrl ?? $this->getResource()::getUrl('index');
    }
}
