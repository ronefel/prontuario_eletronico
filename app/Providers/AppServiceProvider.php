<?php

namespace App\Providers;

use App\Adapters\DatabaseAdapter;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Support\Assets\AlpineComponent;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use League\Flysystem\Filesystem;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Storage::extend('database', function (Application $app, array $config) {
            $adapter = new DatabaseAdapter;

            return new FilesystemAdapter(
                new Filesystem($adapter, $config),
                $adapter,
                $config
            );
        });

        Model::unguard();

        FilamentAsset::register([
            AlpineComponent::make('ckeditor-component', __DIR__.'/../../resources/js/dist/components/ckeditor-component.js'),
        ]);

        // Configuração do DateTimePicker para usar o timezone do usuário
        DateTimePicker::configureUsing(function (DateTimePicker $dateTimePicker): void {
            $dateTimePicker->timezone(optional(Auth::user())->timezone ?? config('app.timezone'));
        });

        // Configuração do DatePicker para usar o timezone UTC
        DatePicker::configureUsing(function (DatePicker $datePicker): void {
            $datePicker->timezone(config('app.timezone'));
        });

        // Registro do Observer para o model de Agenda
        \App\Models\Agenda::observe(\App\Observers\AgendaObserver::class);
    }
}
