<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KitResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class KitResource extends Resource
{
    protected static ?string $modelLabel = 'Kit de Aplicação';

    protected static ?string $navigationGroup = 'Cadastros';

    protected static ?int $navigationSort = 103;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nome')
                    ->label('Nome do Kit')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('descricao')
                    ->label('Descrição')
                    ->maxLength(500)
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('ativo')
                    ->label('Ativo')
                    ->default(true)
                    ->required(),
                Forms\Components\Section::make('Itens do Kit')
                    ->schema([
                        \App\Filament\Forms\Components\RepeaterInline::make('itens')
                            ->relationship('itens')
                            ->schema([
                                Forms\Components\Select::make('produto_id')
                                    ->label('Produto')
                                    ->hiddenLabel()
                                    ->options(\App\Models\Produto::all()->pluck('nome', 'id'))
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->distinct()
                                    ->columnSpan(4)
                                    ->disableOptionsWhenSelectedInSiblingRepeaterItems(),
                                Forms\Components\TextInput::make('quantidade')
                                    ->label('Quantidade Total')
                                    ->hiddenLabel()
                                    ->numeric()
                                    ->default(1)
                                    ->minValue(1)
                                    ->required(),
                            ])
                            ->columns(5)
                            ->columnSpanFull()
                            ->defaultItems(1)
                            ->addActionLabel('Adicionar Produto'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('#')
                    ->sortable(),
                Tables\Columns\TextColumn::make('nome')
                    ->label('Nome')
                    ->searchable(),
                Tables\Columns\IconColumn::make('ativo')
                    ->label('Ativo')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i', Auth::user()->timezone),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListKits::route('/'),
            'create' => Pages\CreateKit::route('/create'),
            'edit' => Pages\EditKit::route('/{record}/edit'),
        ];
    }
}
