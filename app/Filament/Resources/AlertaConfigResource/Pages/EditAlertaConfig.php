<?php

namespace App\Filament\Resources\AlertaConfigResource\Pages;

use App\Filament\Resources\AlertaConfigResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAlertaConfig extends EditRecord
{
    protected static string $resource = AlertaConfigResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->previousUrl ?? $this->getResource()::getUrl('index');
    }
}
