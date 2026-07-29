<?php

namespace App\Filament\Resources\NotasFiscais\Pages;

use App\Filament\Resources\NotasFiscais\NotaFiscalResource;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditNotaFiscal extends EditRecord
{
    protected static string $resource = NotaFiscalResource::class;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        if (! in_array($this->getRecord()->status, ['rascunho', 'rejeitada'])) {
            Notification::make()
                ->title('Edição Não Permitida')
                ->body('Apenas notas fiscais em rascunho ou rejeitadas podem ser editadas.')
                ->warning()
                ->send();

            $this->redirect($this->getResource()::getUrl('view', ['record' => $this->getRecord()]));
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
}
