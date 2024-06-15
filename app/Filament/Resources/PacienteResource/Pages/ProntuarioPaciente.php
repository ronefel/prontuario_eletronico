<?php

namespace App\Filament\Resources\PacienteResource\Pages;

use AmidEsfahani\FilamentTinyEditor\TinyEditor;
use App\Models\Paciente;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Resources\Pages\PageRegistration;
use Filament\Support\Enums\MaxWidth;
use Filament\Panel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Route as RouteFacade;
use Livewire\Attributes\Locked;

class ProntuarioPaciente extends Page implements HasForms
{
    use InteractsWithForms;

    #[Locked]
    public Model | int | string | null $paciente;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $title = 'História Clinica';

    protected static string $view = 'filament.pages.prontuario';

    public ?array $data = [];

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('titulo')
                    ->label('Título')
                    ->required(),
                TinyEditor::make('content')
                    ->hiddenLabel()
                    ->fileAttachmentsDisk('public')
                    ->fileAttachmentsVisibility('uploads')
                    ->fileAttachmentsDirectory('uploads')
                    ->profile('default')
                    ->columnSpan('full')
                    ->required(),
                // ...
            ])
            ->statePath('data');
    }

    public function getMaxContentWidth(): MaxWidth
    {
        return MaxWidth::Full;
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

    public function mount(int | string $record): void
    {
        $this->paciente = Paciente::find($record);
    }
}
