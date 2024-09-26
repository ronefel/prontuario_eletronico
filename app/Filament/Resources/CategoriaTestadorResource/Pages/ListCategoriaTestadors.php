<?php

namespace App\Filament\Resources\CategoriaTestadorResource\Pages;

use App\Filament\Resources\CategoriaTestadorResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCategoriaTestadors extends ListRecords
{
    protected static string $resource = CategoriaTestadorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
