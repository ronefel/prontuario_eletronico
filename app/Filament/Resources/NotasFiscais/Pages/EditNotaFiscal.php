<?php

namespace App\Filament\Resources\NotasFiscais\Pages;

use App\Filament\Resources\NotasFiscais\NotaFiscalResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditNotaFiscal extends EditRecord
{
    protected static string $resource = NotaFiscalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
