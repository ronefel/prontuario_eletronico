<?php

namespace App\Filament\Resources\ModeloResource\Pages;

use App\Filament\Resources\ModeloResource;
use App\Http\Helpers\AgentHelper;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Filament\Support\Enums\MaxWidth;

class ManageModelos extends ManageRecords
{
    protected static string $resource = ModeloResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->modalWidth(AgentHelper::isMobile() ? MaxWidth::Screen : MaxWidth::FiveExtraLarge),
        ];
    }
}
