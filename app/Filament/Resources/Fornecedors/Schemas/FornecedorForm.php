<?php

namespace App\Filament\Resources\Fornecedors\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class FornecedorForm
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
                ->required(),
            TextInput::make('email')
                ->email()
                ->nullable(),
            TextInput::make('telefone')
                ->nullable(),
            Textarea::make('endereco')
                ->nullable(),
            TextInput::make('prazo_entrega')
                ->numeric()
                ->default(7),
            Select::make('status')
                ->options(['ativo' => 'Ativo', 'inativo' => 'Inativo'])
                ->default('ativo'),
        ];
    }
}
