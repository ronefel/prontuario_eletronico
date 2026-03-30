<?php

namespace App\Filament\Resources\CategoriaTestadors\Pages;

use App\Filament\Resources\CategoriaTestadors\CategoriaTestadorResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCategoriaTestadors extends ListRecords
{
    protected static string $resource = CategoriaTestadorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
