<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SettingsResource\Pages;
use App\Forms\Components\CKEditor;
use App\Models\Setting;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SettingsResource extends Resource
{
    protected static ?string $model = Setting::class;
    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';
    protected static ?int $navigationSort = 999;
    protected static ?string $modelLabel = 'Configurações';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('label')
                    ->label('Configuração')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('value')
                    ->label('Valor')
                    ->sortable()
                    ->searchable()
                    ->extraAttributes(['style' => 'white-space: normal', 'class' => 'document-content'])
                    ->html(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->form(function (Setting $record) {
                        return match ($record->type) {
                            'select' => [
                                Select::make('value')
                                    ->label($record->label)
                                    ->options($record->attributes['options'])
                            ],
                            'number' => [
                                TextInput::make('value')
                                    ->label($record->label)
                                    ->type('number')
                            ],
                            'text-editor' => [
                                CKEditor::make('value')
                                    ->label($record->label)
                            ],
                            default => [
                                TextInput::make('value')
                                    ->label($record->label)
                            ]
                        };
                    })
                    ->modalAutofocus(false),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageSettings::route('/'),
        ];
    }
}
