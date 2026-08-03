<?php

namespace App\Filament\Resources\NotasFiscais\Pages;

use App\Filament\Resources\NotasFiscais\NotaFiscalResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListNotasFiscais extends ListRecords
{
    protected static string $resource = NotaFiscalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nova Nota Fiscal'),
        ];
    }
}
