<?php

namespace App\Filament\Resources\AlertaConfigResource\Pages;

use App\Filament\Resources\AlertaConfigResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAlertaConfig extends CreateRecord
{
    protected static string $resource = AlertaConfigResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->previousUrl ?? $this->getResource()::getUrl('index');
    }
}
