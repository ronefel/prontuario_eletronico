<?php

namespace App\Filament\Resources\Cidades\Pages;

use App\Filament\Resources\Cidades\CidadeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageCidades extends ManageRecords
{
    protected static string $resource = CidadeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
