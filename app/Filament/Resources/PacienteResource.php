<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PacienteResource\Pages;
use App\Filament\Resources\PacienteResource\RelationManagers;
use App\Forms\Components\Cep;
use App\Models\Cidade;
use App\Models\Paciente;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PacienteResource extends Resource
{
    protected static ?string $model = Paciente::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Fieldset::make('Dados pessoais')->schema([
                    TextInput::make('nome')
                        ->required()
                        ->dehydrateStateUsing(fn (string $state): string => ucwords(strtolower($state))), // Capitaliza a primeira letra de cada palavra
                    Grid::make()->schema([
                        DatePicker::make('nascimento')
                            ->required(),
                        TextInput::make('idade')
                            ->readOnly()
                    ])->columns(['md' => 2])
                        ->columnSpan(1),

                    Radio::make('sexo')
                        ->label('Sexo Biológico')
                        ->options([
                            'F' => 'Feminino',
                            'M' => 'Masculino',
                        ])
                        ->inline()
                        ->inlineLabel(false)
                        ->required(),
                    TextInput::make('cpf')
                        ->required()
                        ->unique(ignorable: fn (?Paciente $record): ?Paciente => $record)
                        ->mask('999.999.999-99')
                        ->placeholder('000.000.000-00')
                        ->minLength(14),
                ])->columns(['md' => 2]),

                Fieldset::make('Contato')->schema([
                    TextInput::make('email')
                        ->email()
                        ->dehydrateStateUsing(function ($state) {
                            return strtolower($state) ?? null;
                        }),
                    TextInput::make('celular')
                        ->tel()
                        ->mask('(99) 99999-9999')
                        ->placeholder('(00) 00000-0000')
                        ->dehydrateStateUsing(function ($state) {
                            return preg_replace('/[^0-9]/', '', $state) ?? null;
                        }),
                ])->columns(['md' => 2]),

                Fieldset::make('Endereço')->schema([
                    Cep::make('cep')
                        ->label('CEP')
                        ->viaCep(
                            setFields: [
                                'logradouro' => 'logradouro',
                                'numero' => 'numero',
                                'bairro' => 'bairro'
                            ],
                        ),
                    TextInput::make('logradouro'),
                    TextInput::make('numero'),
                    TextInput::make('complemento'),
                    TextInput::make('bairro'),
                    Select::make('cidade_id')
                        ->relationship(
                            name: 'cidade',
                            modifyQueryUsing: fn (Builder $query) => $query->orderBy('nome')->orderBy('uf')
                        )
                        ->getOptionLabelFromRecordUsing(fn (?Cidade $cidade) => $cidade?->nome . ' - ' . $cidade?->uf)
                        ->searchable(['nome'])
                        ->preload()
                        ->createOptionForm(CidadeResource::formFields())
                ])->columns(['md' => 2]),

                Textarea::make('observacao')->rows(4)->columnSpanFull(),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nome'),
                Tables\Columns\TextColumn::make('nascimento'),
                Tables\Columns\TextColumn::make('sexo'),
                Tables\Columns\TextColumn::make('cpf'),
                Tables\Columns\TextColumn::make('email'),
                Tables\Columns\TextColumn::make('celular'),
            ])
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPacientes::route('/'),
            'create' => Pages\CreatePaciente::route('/create'),
            'edit' => Pages\EditPaciente::route('/{record}/edit'),
        ];
    }
}
