<?php

namespace App\Filament\Resources\MovimentacaoResource\Pages;

use App\Filament\Resources\MovimentacaoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMovimentacao extends EditRecord
{
    protected static string $resource = MovimentacaoResource::class;

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
}
