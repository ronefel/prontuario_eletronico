<?php

namespace App\Filament\Resources\PacienteResource\Pages;

use AmidEsfahani\FilamentTinyEditor\TinyEditor;
use App\Enums\ProntuarioTipoEnum;
use App\Filament\Resources\PacienteResource;
use App\Forms\Components\CKEditor;
use App\Http\Helpers\AgentHelper;
use App\Models\Paciente;
use App\Models\Prontuario;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Resources\Pages\PageRegistration;
use Filament\Panel;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Route as RouteFacade;
use Livewire\Attributes\Locked;

class ProntuarioPaciente extends Page
{
    // use InteractsWithForms;

    #[Locked]
    public Paciente | int | string | null $paciente;
    public Prontuario | int | string | null $prontuario;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $title = ' ';

    protected static string $view = 'filament.pages.prontuario';

    public bool $isMobile = false;

    public bool $formClosed = true;

    public ?array $data = [];

    public function mount(int | string $record): void
    {
        $this->paciente = Paciente::find($record);

        $this->isMobile = AgentHelper::isMobile();

        $this->form->fill([
            'data' => now()->startOfDay(),
            'tipo' => 'prontuario',
        ]);
    }

    public function getBreadcrumbs(): array
    {
        return [
            PacienteResource::getUrl() => 'Pacientes',
            PacienteResource::getUrl('edit', ['record' => $this->paciente]) => $this->paciente->nome,
            'Prontuário'
        ];
    }

    public static function formFields(array $newField = []): array
    {
        $fields = [
            Grid::make()->schema([
                ToggleButtons::make('tipo')
                    ->inline()
                    ->grouped()
                    ->required()
                    ->hiddenLabel()
                    ->options(ProntuarioTipoEnum::class)
                    ->columnSpan(2),
                DatePicker::make('data')
                    ->native(AgentHelper::isMobile())
                    ->displayFormat('d/m/Y')
                    ->firstDayOfWeek(7)
                    ->seconds(false)
                    ->closeOnDateSelection()
                    ->maxDate(now()->endOfDay())
                    ->hiddenLabel()
                    ->required(),
            ])->columns(['sm' => 3, 'md' => 3, 'lg' => 3, 'xl' => 3, '2xl' => 3]),
            CKEditor::make('descricao')
                ->hiddenLabel()
                ->required(),
        ];

        return array_merge($fields, $newField);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema(self::formFields())
            ->statePath('data');
    }

    private function resetarForm()
    {
        $this->data = [];
        $this->form->fill([
            'data' => now()->startOfDay(),
            'tipo' => 'prontuario',
        ]);
        $this->dispatch('formReseted');
    }

    // public function getMaxContentWidth(): MaxWidth
    // {
    //     return MaxWidth::Full;
    // }

    public function showForm()
    {
        $this->formClosed = false;
    }

    public function cancel()
    {
        $this->formClosed = true;
        $this->resetarForm();
    }

    public static function route(string $path): PageRegistration
    {
        return new PageRegistration(
            page: static::class,
            route: fn (Panel $panel): Route => RouteFacade::get($path, static::class)
                ->middleware(static::getRouteMiddleware($panel))
                ->withoutMiddleware(static::getWithoutRouteMiddleware($panel)),
        );
    }

    public function create(): void
    {
        $data = $this->form->getState();
        // $data['data'] = Carbon::parse($data['data'])->setTimezone('America/Porto_Velho')->format('Y-m-d');

        $prontuario = new Prontuario($data);

        $this->paciente->prontuarios()->save($prontuario);

        Notification::make()
            ->title('Prontuário salvo com sucesso!')
            ->success()
            ->send();

        $this->resetarForm();

        $this->formClosed = true;
    }

    public function edit($data): void
    {
        $prontuario = $this->paciente->prontuarios()->find($data['id']);

        if ($prontuario) {
            $prontuario->fill($data);

            $this->paciente->prontuarios()->save($prontuario);

            Notification::make()
                ->title('Prontuário atualizado com sucesso!')
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Prontuário não encontrado ou não pertence a este paciente.')
                ->error()
                ->send();
        }
    }

    public function delete(Prontuario $prontuario): void
    {
        if ($prontuario->paciente_id === $this->paciente->id) {
            $this->prontuario->delete();
            $this->paciente->refresh();

            Notification::make()
                ->title('Evento excluído com sucesso!')
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Evento não encontrado ou não pertence a este paciente.')
                ->error()
                ->send();
        }
    }

    public function editAction(): Action
    {
        return Action::make('edit')
            ->form(self::formFields([Hidden::make('id')]))
            ->fillForm(function (array $arguments) {
                $this->prontuario = Prontuario::find($arguments['prontuario']);
                return [
                    'id' => $this->prontuario->id,
                    'data' => $this->prontuario->data,
                    'descricao' => $this->prontuario->descricao,
                    'tipo' => $this->prontuario->tipo
                ];
            })
            ->action(function (array $data): void {
                $this->edit($data);
            })
            ->extraModalFooterActions([
                Action::make('Excluir')
                    ->requiresConfirmation()
                    ->modalIcon('heroicon-o-trash')
                    ->modalHeading('Excluir evento')
                    ->modalDescription('Tem certeza de que deseja excluir este evento? Isto não pode ser desfeito.')
                    ->modalSubmitActionLabel('Sim, exclua-o')
                    ->action(function () {
                        $this->delete($this->prontuario);
                    })
                    ->cancelParentActions()
                    ->color('danger')
                    ->extraAttributes(['style' => 'position: absolute; right: 24px;']),
            ])
            ->icon('heroicon-m-pencil-square')
            ->iconButton()
            ->modalWidth(AgentHelper::isMobile() ? MaxWidth::Screen : MaxWidth::FourExtraLarge)
            ->extraModalWindowAttributes(AgentHelper::isMobile() ? ['style' => 'overflow: auto'] : [])
            ->modalHeading(' ');
    }
}
