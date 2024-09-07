<?php

namespace App\Filament\Resources\MascaraResource\Pages;

use App\Filament\Resources\MascaraResource;
use App\Http\Helpers\AgentHelper;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Filament\Support\Enums\MaxWidth;

class ManageMascaras extends ManageRecords
{
    protected static string $resource = MascaraResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->modalWidth(AgentHelper::isMobile() ? MaxWidth::Screen : MaxWidth::FiveExtraLarge),
        ];
    }
}
