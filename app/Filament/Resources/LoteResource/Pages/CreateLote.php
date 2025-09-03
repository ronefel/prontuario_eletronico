<?php

namespace App\Filament\Resources\LoteResource\Pages;

use App\Filament\Resources\LoteResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLote extends CreateRecord
{
    protected static string $resource = LoteResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->previousUrl ?? $this->getResource()::getUrl('index');
    }
}
