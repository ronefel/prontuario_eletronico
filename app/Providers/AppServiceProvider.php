<?php

namespace App\Providers;

use App\Adapters\DatabaseAdapter;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Support\Assets\AlpineComponent;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Filesystem\FilesystemAdapter;
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
        // Storage::extend('database', function (Application $app, array $config) {
        //     $adapter = new DatabaseAdapter();

        //     return new FilesystemAdapter(
        //         new Filesystem($adapter, $config),
        //         $adapter,
        //         $config
        //     );
        // });
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

        TextInput::macro('acoff', function () {
            return $this->autocomplete('no');
        });

        Textarea::macro('acoff', function () {
            return $this->autocomplete('no');
        });

        FilamentAsset::register([
            AlpineComponent::make('ckeditor-component', __DIR__.'/../../resources/js/dist/components/ckeditor-component.js'),
        ]);

        FilamentAsset::register([
            Js::make('ckeditor', asset('vendor/ckeditor/ckeditor.js'))->loadedOnRequest(),
        ]);

        DateTimePicker::configureUsing(function (DateTimePicker $dateTimePicker): void {
            $dateTimePicker->timezone(auth()->user()->timezone);
        });
    }
}
