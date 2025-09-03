<?php

namespace App\Filament\Resources\LocalResource\Pages;

use App\Filament\Resources\LocalResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLocais extends ListRecords
{
    protected static string $resource = LocalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
