<?php

namespace App\Filament\Resources\Categorias\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CategoriaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components(self::getComponents());
    }

    public static function getComponents(): array
    {
        return [
            TextInput::make('nome')
                ->required()
                ->maxLength(255),
            Textarea::make('descricao')
                ->columnSpanFull(),
        ];
    }
}
