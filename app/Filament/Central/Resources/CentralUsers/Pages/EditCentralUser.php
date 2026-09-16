<?php

namespace App\Filament\Central\Resources\CentralUsers\Pages;

use App\Filament\Central\Resources\CentralUsers\CentralUserResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCentralUser extends EditRecord
{
    protected static string $resource = CentralUserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
