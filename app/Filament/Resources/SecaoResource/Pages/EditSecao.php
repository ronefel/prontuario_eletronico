<?php

namespace App\Filament\Resources\SecaoResource\Pages;

use App\Filament\Resources\SecaoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSecao extends EditRecord
{
    protected static string $resource = SecaoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
