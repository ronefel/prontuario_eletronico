<?php

namespace App\Filament\Resources\Locais\Pages;

use App\Filament\Resources\Locais\LocalResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLocal extends EditRecord
{
    protected static string $resource = LocalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->previousUrl ?? $this->getResource()::getUrl('index');
    }
}
