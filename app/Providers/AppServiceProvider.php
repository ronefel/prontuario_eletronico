<?php

namespace App\Providers;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Support\Assets\AlpineComponent;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

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
        Model::unguard();

        TextInput::macro('acoff', function () {
            return $this->autocomplete('no');
        });

        Textarea::macro('acoff', function () {
            return $this->autocomplete('no');
        });

        FilamentAsset::register([
            AlpineComponent::make('ckeditor-component', '/components/ckeditor-component.js'),
        ]);

        FilamentAsset::register([
            Js::make('ckeditor', asset('vendor/ckeditor/ckeditor.js'))->loadedOnRequest(),
        ]);

        DateTimePicker::configureUsing(function (DateTimePicker $checkbox): void {
            $checkbox->timezone(auth()->user()->timezone);
        });
    }
}
