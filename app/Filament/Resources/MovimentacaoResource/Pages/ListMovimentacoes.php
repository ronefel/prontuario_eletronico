<?php

namespace App\Filament\Resources\MovimentacaoResource\Pages;

use App\Filament\Resources\MovimentacaoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMovimentacoes extends ListRecords
{
    protected static string $resource = MovimentacaoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
