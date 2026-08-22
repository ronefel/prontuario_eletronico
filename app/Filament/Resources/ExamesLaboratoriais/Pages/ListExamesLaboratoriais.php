<?php

namespace App\Filament\Resources\ExamesLaboratoriais\Pages;

use App\Filament\Resources\ExamesLaboratoriais\ExameLaboratorialResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListExamesLaboratoriais extends ListRecords
{
    protected static string $resource = ExameLaboratorialResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
