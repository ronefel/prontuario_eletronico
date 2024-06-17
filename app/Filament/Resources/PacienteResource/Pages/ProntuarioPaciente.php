<?php

namespace App\Filament\Resources\PacienteResource\Pages;

use AmidEsfahani\FilamentTinyEditor\TinyEditor;
use App\Models\Paciente;
use App\Models\Prontuario;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Resources\Pages\PageRegistration;
use Filament\Panel;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Route as RouteFacade;
use Livewire\Attributes\Locked;

class ProntuarioPaciente extends Page
{
    // use InteractsWithForms;

    #[Locked]
    public Paciente | int | string | null $paciente;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $title = 'Protuário';

    protected static string $view = 'filament.pages.prontuario';

    public bool $formClosed = true;

    public ?array $data = [];

    public function mount(int | string $record): void
    {
        $this->paciente = Paciente::find($record);

        $this->form->fill([
            'data' => now(),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make()->schema([
                    DatePicker::make('data')
                        ->native(false)
                        ->timezone('America/Porto_Velho')
                        ->displayFormat('d/m/Y')
                        ->firstDayOfWeek(7)
                        ->closeOnDateSelection()
                        ->maxDate(now()->timezone('America/Porto_Velho'))
                        ->required()
                ])->columns(['sm' => 2]),
                TinyEditor::make('descricao')
                    ->hiddenLabel()
                    ->fileAttachmentsDisk('public')
                    ->fileAttachmentsVisibility('uploads')
                    ->fileAttachmentsDirectory('uploads')
                    ->profile('default')
                    ->required(),
                // ...
            ])
            ->statePath('data');
    }

    // public function getMaxContentWidth(): MaxWidth
    // {
    //     return MaxWidth::Full;
    // }

    public function showForm() {
        $this->formClosed = false;
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
        $prontuario = new Prontuario($data);

        $this->paciente->prontuarios()->save($prontuario);

        Notification::make()
            ->title('Prontuário salvo com sucesso!')
            ->success()
            ->send();

        $this->data = [];

        $this->formClosed = true;
    }
}
