<?php

namespace App\Filament\Resources\Locais\Pages;

use App\Filament\Resources\Locais\LocalResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLocais extends ListRecords
{
    protected static string $resource = LocalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
