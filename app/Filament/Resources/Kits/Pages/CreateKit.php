<?php

namespace App\Filament\Resources\Kits\Pages;

use App\Filament\Resources\Kits\KitResource;
use Filament\Resources\Pages\CreateRecord;

class CreateKit extends CreateRecord
{
    protected static string $resource = KitResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->previousUrl ?? $this->getResource()::getUrl('index');
    }
}
