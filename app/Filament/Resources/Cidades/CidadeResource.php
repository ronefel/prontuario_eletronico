<?php

namespace App\Filament\Resources\Cidades;

use App\Filament\Resources\Cidades\Pages\ManageCidades;
use App\Models\Cidade;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Validation\Rules\Unique;
use UnitEnum;

class CidadeResource extends Resource
{
    protected static ?string $model = Cidade::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMap;

    protected static string|UnitEnum|null $navigationGroup = 'Cadastros';

    protected static ?int $navigationSort = 105;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components(self::formFields())->columns(['md' => 2]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('Cidade')
            ->columns([
                TextColumn::make('nome')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('uf'),
            ])
            ->defaultSort('nome', 'asc')
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageCidades::route('/'),
        ];
    }

    public static function formFields(): array
    {
        return [
            TextInput::make('nome')
                ->required()
                ->live(onBlur: true)
                ->afterStateUpdated(fn (Set $set, ?string $state): string => $set('nome', ucwords(strtolower($state)))) // Capitaliza a primeira letra de cada palavra
                ->unique(
                    ignoreRecord: true,
                    modifyRuleUsing: function (Unique $rule, Get $get) {
                        return $rule->where('uf', $get('uf'));
                    }
                )
                ->validationMessages([
                    'unique' => 'A cidade informada já existe na base de dados.',
                ]),
            Select::make('uf')
                ->label('UF')
                ->required()
                ->options([
                    'AC' => 'Acre',
                    'AL' => 'Alagoas',
                    'AM' => 'Amazonas',
                    'AP' => 'Amapá',
                    'BA' => 'Bahia',
                    'CE' => 'Ceará',
                    'DF' => 'Distrito Federal',
                    'ES' => 'Espírito Santo',
                    'GO' => 'Goiás',
                    'MA' => 'Maranhão',
                    'MG' => 'Minas Gerais',
                    'MS' => 'Mato Grosso do Sul',
                    'MT' => 'Mato Grosso',
                    'PA' => 'Pará',
                    'PB' => 'Paraíba',
                    'PE' => 'Pernambuco',
                    'PI' => 'Piauí',
                    'PR' => 'Paraná',
                    'RJ' => 'Rio de Janeiro',
                    'RN' => 'Rio Grande do Norte',
                    'RO' => 'Rondônia',
                    'RR' => 'Roraima',
                    'RS' => 'Rio Grande do Sul',
                    'SC' => 'Santa Catarina',
                    'SE' => 'Sergipe',
                    'SP' => 'São Paulo',
                    'TO' => 'Tocantins',
                ])->searchable(),
        ];
    }
}
