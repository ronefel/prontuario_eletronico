<?php

namespace App\Filament\Central\Resources\CentralUsers\Pages;

use App\Filament\Central\Resources\CentralUsers\CentralUserResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCentralUsers extends ListRecords
{
    protected static string $resource = CentralUserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
