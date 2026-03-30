<?php

namespace App\Filament\Resources\Settings;

use App\Filament\Resources\Settings\Pages\ManageSettings;
use App\Forms\Components\CKEditor;
use App\Models\Setting;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class SettingResource extends Resource
{
    protected static ?string $model = Setting::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedWrenchScrewdriver;

    protected static string|UnitEnum|null $navigationGroup = 'Sistema';

    protected static ?int $navigationSort = 401;

    protected static ?string $modelLabel = 'Configuração';

    protected static ?string $pluralModelLabel = 'Configurações';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('Setting')
            ->columns([
                TextColumn::make('label')
                    ->label('Configuração')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('value')
                    ->label('Valor')
                    ->sortable()
                    ->searchable()
                    ->extraAttributes(['style' => 'white-space: normal', 'class' => 'document-content'])
                    ->html(),
            ])
            ->recordActions([
                EditAction::make()
                    ->hiddenLabel()
                    ->schema(function (Setting $record) {
                        return match ($record->type) {
                            'select' => [
                                Select::make('value')
                                    ->label($record->label)
                                    ->options($record->attributes['options']),
                            ],
                            'number' => [
                                TextInput::make('value')
                                    ->label($record->label)
                                    ->type('number'),
                            ],
                            'text-editor' => [
                                CKEditor::make('value')
                                    ->label($record->label),
                            ],
                            default => [
                                TextInput::make('value')
                                    ->label($record->label),
                            ]
                        };
                    })
                    ->modalAutofocus(false),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageSettings::route('/'),
        ];
    }
}
