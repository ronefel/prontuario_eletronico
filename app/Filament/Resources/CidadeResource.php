<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CidadeResource\Pages;
use App\Models\Cidade;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Validation\Rules\Unique;

class CidadeResource extends Resource
{
    protected static ?string $model = Cidade::class;

    protected static ?string $navigationIcon = 'heroicon-o-map';

    protected static ?string $navigationGroup = 'Cadastros';

    protected static ?int $navigationSort = 105;

    public static function form(Form $form): Form
    {
        return $form
            ->schema(self::formFields())->columns(['md' => 2]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nome')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('uf'),
            ])
            ->defaultSort('nome', 'asc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageCidades::route('/'),
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
