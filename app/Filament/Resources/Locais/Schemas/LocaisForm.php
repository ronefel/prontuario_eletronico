<?php

namespace App\Filament\Resources\Locais\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class LocaisForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nome')->required(),
                Textarea::make('endereco')->nullable(),
                TextInput::make('capacidade')->numeric()->nullable(),
            ]);
    }
}
