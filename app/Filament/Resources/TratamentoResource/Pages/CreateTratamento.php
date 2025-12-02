<?php

namespace App\Filament\Resources\TratamentoResource\Pages;

use App\Filament\Resources\TratamentoResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTratamento extends CreateRecord
{
    protected static string $resource = TratamentoResource::class;

    public ?int $pacienteId = null;

    public function mount(): void
    {
        $this->pacienteId = (int) request()->route('pacienteId');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['paciente_id'] = $this->pacienteId;

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return TratamentoResource::getUrl('index', ['pacienteId' => $this->pacienteId]);
    }
}
