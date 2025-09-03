<?php

namespace App\Filament\Resources\LocalResource\Pages;

use App\Filament\Resources\LocalResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLocal extends CreateRecord
{
    protected static string $resource = LocalResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->previousUrl ?? $this->getResource()::getUrl('index');
    }
}
