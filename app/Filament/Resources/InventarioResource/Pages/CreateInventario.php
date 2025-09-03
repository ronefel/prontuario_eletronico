<?php

namespace App\Filament\Resources\InventarioResource\Pages;

use App\Filament\Resources\InventarioResource;
use Filament\Resources\Pages\CreateRecord;

class CreateInventario extends CreateRecord
{
    protected static string $resource = InventarioResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->previousUrl ?? $this->getResource()::getUrl('index');
    }
}
