<?php

namespace App\Filament\Resources\PacienteResource\Pages;

use App\Filament\Resources\PacienteResource;
use App\Forms\Components\CKEditor;
use App\Http\Helpers\AgentHelper;
use App\Models\Paciente;
use App\Models\Prontuario;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\ToggleButtons;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Resources\Pages\PageRegistration;
use Filament\Panel;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Route as RouteFacade;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Locked;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

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
        $this->paciente = Paciente::findOrFail($record);

        $this->isMobile = AgentHelper::isMobile();

        // $this->form->fill([
        //     'data' => now()->startOfDay(),
        //     'tipo' => 'atendimento',
        // ]);
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
                // ToggleButtons::make('tipo')
                //     ->inline()
                //     ->grouped()
                //     ->required()
                //     ->hiddenLabel()
                //     ->options(ProntuarioTipoEnum::class)
                //     ->columnSpan(2),
                DateTimePicker::make('data')
                    ->seconds(false)
                    ->native()
                    ->displayFormat('d/m/Y H:i')
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
            FileUpload::make('arquivos')
                ->disk('database')
                ->preserveFilenames()
                ->multiple()
                ->minSize(1)
                ->previewable(false)
                ->openable()
                ->getUploadedFileNameForStorageUsing(
                    fn(TemporaryUploadedFile $file) => self::generateUniqueFileNameForStorageUsing($file)
                )

        ];

        return array_merge($fields, $newField);
    }

    // public function form(Form $form): Form
    // {
    //     return $form
    //         ->schema(self::formFields())
    //         ->statePath('data');
    // }

    // private function resetarForm()
    // {
    //     $this->data = [];
    //     $this->form->fill([
    //         'data' => now()->startOfDay(),
    //         'tipo' => 'atendimento',
    //         'descricao' => ''
    //     ]);
    //     $this->dispatch('formReseted');
    // }

    // public function getMaxContentWidth(): MaxWidth
    // {
    //     return MaxWidth::Full;
    // }

    public function showForm()
    {
        $this->formClosed = false;
    }

    // public function cancel()
    // {
    //     $this->formClosed = true;
    //     $this->resetarForm();
    // }

    public static function route(string $path): PageRegistration
    {
        return new PageRegistration(
            page: static::class,
            route: fn(Panel $panel): Route => RouteFacade::get($path, static::class)
                ->middleware(static::getRouteMiddleware($panel))
                ->withoutMiddleware(static::getWithoutRouteMiddleware($panel)),
        );
    }

    public function create($data): void
    {
        // $data = $this->form->getState();
        // $data['data'] = Carbon::parse($data['data'])->setTimezone('America/Porto_Velho')->format('Y-m-d');

        $prontuario = new Prontuario($data);

        $this->paciente->prontuarios()->save($prontuario);

        Notification::make()
            ->title('Prontuário salvo com sucesso!')
            ->success()
            ->send();

        // $this->resetarForm();

        $this->formClosed = true;
    }

    public function createAction(): Action
    {
        return Action::make('create')
            ->form(self::formFields())
            ->fillForm(function (array $arguments) {
                return [
                    'data' => now(),
                    'tipo' => 'atendimento',
                ];
            })
            ->action(function (array $data): void {
                $this->create($data);
            })
            ->label('Novo Evento')
            ->modalWidth(AgentHelper::isMobile() ? MaxWidth::Screen : MaxWidth::SixExtraLarge)
            ->extraModalWindowAttributes(AgentHelper::isMobile() ? ['style' => 'overflow: auto'] : ['style' => 'padding: 0px 37.5px'])
            ->modalHeading(' ')
            ->modalAutofocus(false);
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
                    'arquivos' => $this->prontuario->arquivos,
                    // 'tipo' => $this->prontuario->tipo
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
            ->label('Editar')
            ->modalWidth(AgentHelper::isMobile() ? MaxWidth::Screen : MaxWidth::SixExtraLarge)
            ->extraModalWindowAttributes(AgentHelper::isMobile() ? ['style' => 'overflow: auto'] : ['style' => 'padding: 0px 37.5px'])
            ->modalHeading(' ')
            ->extraAttributes(['style' => 'width: 20px'])
            ->modalAutofocus(false);
    }

    public function printAction(): Action
    {
        return Action::make('print')
            ->icon('heroicon-o-printer')
            ->iconButton()
            ->label('Imprimir')
            ->modal()
            ->modalSubmitActionLabel('Imprimir')
            ->modalWidth(MaxWidth::ExtraSmall)
            ->form([
                ToggleButtons::make('layout')
                    ->label('Layout')
                    ->options([
                        'P' => 'Retrato',
                        'L' => 'Paisagem',
                    ])
                    ->default('P')
                    ->grouped(),
                ToggleButtons::make('paper_size')
                    ->label('Tamanho do Papel')
                    ->options([
                        'A4' => 'A4',
                        'A5' => 'A5',
                    ])
                    ->default('A4')
                    ->grouped(),
            ])
            ->action(function ($arguments, $data) {
                // Gerar a URL com os parâmetros selecionados
                $url = route('prontuario.print', [
                    'id' => $arguments['prontuario'],
                    'layout' => $data['layout'],
                    'paper_size' => $data['paper_size'],
                ]);

                // Disparar um evento para abrir a URL em uma nova aba
                $this->dispatch('openUrlInNewTab', ['url' => $url]);
            });
    }

    private static function generateUniqueFileNameForStorageUsing(TemporaryUploadedFile $file): string
    {
        // Obtém o nome original do arquivo e a extensão
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $file->getClientOriginalExtension();

        $counter = 1;
        $newFileName = "{$originalName}.{$extension}";

        // Verifica se o arquivo já existe e gera um novo nome se necessário
        while (Storage::disk('database')->exists($newFileName)) {
            $newFileName = "{$originalName}_{$counter}.{$extension}"; // Cria o novo nome do arquivo
            $counter++;
        }
        return $newFileName;
    }
}
