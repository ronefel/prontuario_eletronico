<?php

namespace App\Filament\Resources\Mascaras\Pages;

use App\Filament\Resources\Mascaras\MascaraResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageMascaras extends ManageRecords
{
    protected static string $resource = MascaraResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
