<?php

namespace App\Filament\Resources\Kits\Schemas;

use App\Filament\Forms\Components\RepeaterInline;
use App\Models\Produto;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class KitForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nome')
                    ->label('Nome do Kit')
                    ->required()
                    ->maxLength(255),
                Toggle::make('ativo')
                    ->label('Ativo')
                    ->default(true)
                    ->required(),
                Textarea::make('descricao')
                    ->label('Descrição')
                    ->maxLength(500)
                    ->columnSpanFull(),
                Section::make('Produtos do Kit')
                    ->schema([
                        RepeaterInline::make('itens')
                            ->hiddenLabel()
                            ->relationship('itens')
                            ->schema([
                                Select::make('produto_id')
                                    ->hiddenLabel()
                                    ->options(Produto::all()->pluck('nome', 'id'))
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->distinct()
                                    ->columnSpan(7)
                                    ->disableOptionsWhenSelectedInSiblingRepeaterItems(),
                                TextInput::make('quantidade')
                                    ->hiddenLabel()
                                    ->numeric()
                                    ->suffix('un')
                                    ->default(1)
                                    ->minValue(1)
                                    ->required()
                                    ->columnSpan(1),
                            ])
                            ->columns(8)
                            ->defaultItems(1)
                            ->addActionLabel('Adicionar Produto'),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
