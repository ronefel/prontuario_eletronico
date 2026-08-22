<?php

namespace App\Filament\Resources\ExamesLaboratoriais\Pages;

use App\Filament\Resources\ExamesLaboratoriais\ExameLaboratorialResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditExameLaboratorial extends EditRecord
{
    protected static string $resource = ExameLaboratorialResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
