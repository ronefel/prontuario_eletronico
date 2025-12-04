<?php

namespace App\Filament\Resources\TratamentoResource\Pages;

use App\Filament\Resources\TratamentoResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListTratamentos extends ListRecords
{
    protected static string $resource = TratamentoResource::class;

    public ?int $pacienteId = null;

    public function getBreadcrumbs(): array
    {
        return [];
    }

    public function mount(?int $pacienteId = null): void
    {
        $this->pacienteId = $pacienteId;
    }

    protected function getTableQuery(): ?Builder
    {
        $query = parent::getTableQuery();

        if ($this->pacienteId) {
            $query->where('paciente_id', $this->pacienteId);
        }

        return $query;
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make()
                ->label('Novo Tratamento')
                ->url(fn () => TratamentoResource::getUrl('create', ['pacienteId' => $this->pacienteId])),
        ];
    }
}
