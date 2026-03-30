<?php

namespace App\Filament\Resources\Tratamentos\Pages;

use App\Filament\Resources\Tratamentos\Tables\TratamentosTable;
use App\Filament\Resources\Tratamentos\TratamentoResource;
use App\Models\Tratamento;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;
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

    public function table(Table $table): Table
    {
        return TratamentosTable::configure(
            $table->query(Tratamento::query()
                ->when(
                    $this->pacienteId,
                    fn (Builder $query) => $query->where('paciente_id', $this->pacienteId)
                )
            )
        );
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Novo Tratamento')
                ->url(fn () => TratamentoResource::getUrl('create', ['pacienteId' => $this->pacienteId])),
        ];
    }
}
