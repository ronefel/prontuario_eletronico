<?php

namespace App\Filament\Resources\Tratamentos\Pages;

use App\Filament\Resources\Tratamentos\TratamentoResource;
use App\Models\Paciente;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

class EditTratamento extends EditRecord
{
    protected static string $resource = TratamentoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
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
            ->url(route('filament.admin.pages.consultorio.{paciente}', ['paciente' => $this->record->paciente_id, 'tab' => 'tratamentos']))
            ->color('gray');
    }

    public function getSubheading(): string|Htmlable|null
    {
        $paciente = Paciente::find($this->record->paciente_id);
        if (! $paciente) {
            return null;
        }

        return new HtmlString(view('filament.pages.cabecalho-paciente', [
            'paciente' => $paciente,
        ])->render());
    }

    // protected function getRedirectUrl(): string
    // {
    //     return $this->previousUrl ?? route('filament.admin.pages.consultorio.{paciente}', ['paciente' => $this->record->paciente_id, 'tab' => 'tratamentos']);
    // }
}
