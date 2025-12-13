<?php

namespace App\Filament\Resources\KitResource\Pages;

use App\Filament\Resources\KitResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditKit extends EditRecord
{
    protected static string $resource = KitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
