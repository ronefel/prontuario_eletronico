<?php

namespace App\Filament\Resources\CampoResource\Pages;

use App\Filament\Resources\CampoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCampos extends ListRecords
{
    protected static string $resource = CampoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
