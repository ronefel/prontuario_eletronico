<?php

namespace App\Filament\Resources\CategoriaTestadors\Schemas;

use App\Models\CategoriaTestador;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CategoriaTestadorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nome')
                    ->required()
                    ->maxLength(255),
                TextInput::make('ordem')
                    ->required()
                    ->default(fn () => str_pad(''.CategoriaTestador::max('ordem') + 1, 2, '0', STR_PAD_LEFT))
                    ->helperText('Ordem de exibição. Exemplo: 01')
                    ->numeric(),
                RichEditor::make('nota')
                    ->helperText('Observação sobre a categoria de testadores')
                    ->toolbarButtons([
                        'bold',
                        'italic',
                        'underline',
                        'strike',
                        'blockquote',
                        'bulletList',
                        'orderedList',
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
