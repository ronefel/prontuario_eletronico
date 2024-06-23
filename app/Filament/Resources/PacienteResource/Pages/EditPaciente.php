<?php

namespace App\Filament\Resources\PacienteResource\Pages;

use App\Filament\Resources\PacienteResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPaciente extends EditRecord
{
    protected static string $resource = PacienteResource::class;

    public function getTitle(): string
    {
        return $this->record->nome;
    }


    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('Prontuário')
                ->url(route('filament.admin.resources.pacientes.protuario', ['record' => $this->record->id])),
            Actions\DeleteAction::make(),
        ];
    }
}
