<?php

namespace App\Filament\Resources\TratamentoResource\Pages;

use App\Filament\Resources\TratamentoResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Js;

class EditTratamento extends EditRecord
{
    protected static string $resource = TratamentoResource::class;

    public ?int $pacienteId = null;

    public ?\App\Models\Paciente $paciente = null;

    public function mount(int|string $record): void
    {
        $this->pacienteId = (int) request()->route('pacienteId');
        $this->paciente = \App\Models\Paciente::find($this->pacienteId);

        parent::mount($record);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    public function getBreadcrumbs(): array
    {
        return [];
    }

    protected function getCancelFormAction(): Action
    {
        return Action::make('cancel')
            ->label('Voltar')
            ->alpineClickHandler('document.referrer ? window.history.back() : (window.location.href = '.Js::from($this->previousUrl ?? static::getResource()::getUrl()).')')
            ->color('gray');
    }

    // protected function getRedirectUrl(): string
    // {
    //     return $this->previousUrl ?? route('filament.admin.pages.consultorio.{paciente}', ['paciente' => $this->pacienteId, 'tab' => 'tratamentos']);
    // }
}
