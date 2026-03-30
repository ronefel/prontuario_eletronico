<?php

namespace App\Filament\Resources\Pacientes\Schemas;

use App\Filament\Resources\Cidades\CidadeResource;
use App\Forms\Components\Cep;
use App\Models\Cidade;
use App\Models\Paciente;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class PacienteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Fieldset::make('Dados pessoais')
                    ->columns(['md' => 2])
                    ->dense()
                    ->schema([
                        TextInput::make('nome')
                            ->required()
                            ->autocomplete(false)
                            ->dehydrateStateUsing(fn (string $state): string => ucwords(strtolower($state))), // Capitaliza a primeira letra de cada palavra
                        Grid::make()
                            ->columns(['md' => 2])
                            ->columnSpan(1)
                            ->schema([
                                DatePicker::make('nascimento')
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Set $set, ?string $state): string => $set('idade', Paciente::calcularIdade($state)))
                                    ->afterStateHydrated(fn (Set $set, ?string $state): bool => $set('idade', Paciente::calcularIdade($state))),
                                TextInput::make('idade')
                                    ->disabled(),
                            ]),
                        Grid::make()
                            ->columns(['md' => 2])
                            ->columnSpan(1)
                            ->schema([
                                Radio::make('sexo')
                                    ->label('Sexo Biológico')
                                    ->options([
                                        'F' => 'Feminino',
                                        'M' => 'Masculino',
                                    ])
                                    ->inline()
                                    ->inlineLabel(false)
                                    ->required(),
                                Select::make('tiposanguineo')
                                    ->label('Tipo Sanguíneo')
                                    ->options([
                                        'A+' => 'A+',
                                        'A-' => 'A-',
                                        'B+' => 'B+',
                                        'B-' => 'B-',
                                        'AB+' => 'AB+',
                                        'AB-' => 'AB-',
                                        'O+' => 'O+',
                                        'O-' => 'O-',
                                    ]),
                            ]),
                        TextInput::make('cpf')
                            ->label('CPF')
                            ->required()
                            ->autocomplete(false)
                            ->unique(ignoreRecord: true)
                            ->mask('999.999.999-99')
                            ->placeholder('000.000.000-00')
                            ->minLength(14),
                    ]),

                Fieldset::make('Contato')
                    ->columns(['md' => 2])
                    ->dense()
                    ->schema([
                        TextInput::make('email')
                            ->label('E-mail')
                            ->email()
                            ->unique(ignoreRecord: true)
                            ->nullable()
                            ->autocomplete(false)
                            ->dehydrateStateUsing(function ($state) {
                                return ! empty($state) ? strtolower($state) : null;
                            }),
                        TextInput::make('celular')
                            ->tel()
                            ->autocomplete(false)
                            ->mask('(99) 99999-9999')
                            ->placeholder('(00) 00000-0000')
                            ->dehydrateStateUsing(function ($state) {
                                return preg_replace('/[^0-9]/', '', $state) ?? null;
                            }),
                    ]),

                Fieldset::make('Endereço')
                    ->columns(['md' => 2])
                    ->dense()
                    ->schema([
                        Cep::make('cep')
                            ->autocomplete(false)
                            ->viaCep(
                                setFields: [
                                    'logradouro' => 'logradouro',
                                    'bairro' => 'bairro',
                                    'localidade' => 'cidade_id',
                                ],
                            ),
                        TextInput::make('logradouro')
                            ->autocomplete(false),
                        TextInput::make('numero')
                            ->label('Número')
                            ->autocomplete(false),
                        TextInput::make('complemento')
                            ->autocomplete(false),
                        TextInput::make('bairro')
                            ->autocomplete(false),

                        Select::make('cidade_id')
                            ->relationship(
                                name: 'cidade',
                                modifyQueryUsing: fn (Builder $query) => $query->orderBy('nome')->orderBy('uf')
                            )
                            ->getOptionLabelFromRecordUsing(fn (?Cidade $cidade) => $cidade?->nome.' - '.$cidade?->uf)
                            ->searchable()
                            ->preload()
                            ->createOptionForm(CidadeResource::formFields())->createOptionModalHeading('Criar Cidade')
                            ->editOptionForm(CidadeResource::formFields())->editOptionModalHeading('Editar Cidade'),
                    ]),

                Textarea::make('observacao')
                    ->label('Observação')
                    ->autocomplete(false)->rows(4)->columnSpanFull(),
            ]);
    }
}
