<?php

namespace App\Filament\Resources\TratamentoResource\Pages;

use App\Filament\Resources\TratamentoResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

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

    protected function getCancelFormAction(): Action
    {
        return Action::make('cancel')
            ->label('Voltar')
            ->url(route('filament.admin.pages.consultorio.{paciente}', ['paciente' => $this->pacienteId, 'tab' => 'tratamentos']))
            ->color('gray');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['paciente_id'] = $this->pacienteId;

        return $data;
    }

    public function getSubheading(): string|Htmlable|null
    {
        if (! $this->paciente) {
            return null;
        }

        return new HtmlString(view('filament.pages.cabecalho-paciente', [
            'paciente' => $this->paciente,
        ])->render());
    }

    // protected function getRedirectUrl(): string
    // {
    //     return route('filament.admin.pages.consultorio.{paciente}', ['paciente' => $this->pacienteId, 'tab' => 'tratamentos']);
    // }
}
