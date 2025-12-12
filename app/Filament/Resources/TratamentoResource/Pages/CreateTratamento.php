<?php

namespace App\Filament\Resources\TratamentoResource\Pages;

use App\Filament\Resources\TratamentoResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTratamento extends CreateRecord
{
    protected static string $resource = TratamentoResource::class;

    protected static ?string $title = 'Novo Tratamento';

    public ?int $pacienteId = null;

    public function getBreadcrumbs(): array
    {
        return [];
    }

    public ?\App\Models\Paciente $paciente = null;

    public function mount(): void
    {
        $this->pacienteId = (int) request()->route('pacienteId');
        $this->paciente = \App\Models\Paciente::find($this->pacienteId);

        parent::mount();
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['paciente_id'] = $this->pacienteId;

        return $data;
    }

    // protected function getRedirectUrl(): string
    // {
    //     return route('filament.admin.pages.consultorio.{paciente}', ['paciente' => $this->pacienteId, 'tab' => 'tratamentos']);
    // }
}
