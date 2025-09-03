<?php

namespace App\Filament\Resources\AlertaConfigResource\Pages;

use App\Filament\Resources\AlertaConfigResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAlertaConfigs extends ListRecords
{
    protected static string $resource = AlertaConfigResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
