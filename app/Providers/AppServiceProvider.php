<?php

namespace App\Providers;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
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
    }
}
