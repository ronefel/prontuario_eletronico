<?php

namespace App\Filament\Resources\NotasFiscais\Schemas;

use App\Models\Paciente;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;

class NotaFiscalForm
{
    public static function configure($schema)
    {
        return $schema
            ->components([
                Grid::make(2)->schema([
                    Fieldset::make('Tomador do Serviço (Paciente)')
                        ->schema([
                            Select::make('paciente_id')
                                ->label('Paciente / Tomador')
                                ->options(Paciente::all()->pluck('nome', 'id'))
                                ->searchable()
                                ->required()
                                ->columnSpan(2),
                        ])
                        ->columnSpan(2),

                    Fieldset::make('Detalhamento dos Valores do Serviço')
                        ->schema([
                            TextInput::make('valor_servicos')
                                ->label('Valor dos Serviços (R$)')
                                ->numeric()
                                ->prefix('R$')
                                ->required()
                                ->columnSpan(1),

                            TextInput::make('aliquota_iss')
                                ->label('Alíquota ISS (%)')
                                ->numeric()
                                ->default(2.00)
                                ->required()
                                ->columnSpan(1),

                            TextInput::make('valor_iss')
                                ->label('Valor do ISS (R$)')
                                ->numeric()
                                ->prefix('R$')
                                ->default(0.00)
                                ->columnSpan(1),

                            TextInput::make('valor_deducoes')
                                ->label('Deduções (R$)')
                                ->numeric()
                                ->prefix('R$')
                                ->default(0.00)
                                ->columnSpan(1),

                            TextInput::make('valor_pis')
                                ->label('PIS (R$)')
                                ->numeric()
                                ->prefix('R$')
                                ->default(0.00)
                                ->columnSpan(1),

                            TextInput::make('valor_cofins')
                                ->label('COFINS (R$)')
                                ->numeric()
                                ->prefix('R$')
                                ->default(0.00)
                                ->columnSpan(1),

                            TextInput::make('valor_inss')
                                ->label('INSS (R$)')
                                ->numeric()
                                ->prefix('R$')
                                ->default(0.00)
                                ->columnSpan(1),

                            TextInput::make('valor_ir')
                                ->label('IR (R$)')
                                ->numeric()
                                ->prefix('R$')
                                ->default(0.00)
                                ->columnSpan(1),

                            TextInput::make('valor_csll')
                                ->label('CSLL (R$)')
                                ->numeric()
                                ->prefix('R$')
                                ->default(0.00)
                                ->columnSpan(1),
                        ])
                        ->columnSpan(2),

                    Fieldset::make('Informações da Prestação')
                        ->schema([
                            TextInput::make('item_lista_servico')
                                ->label('Item Lista Serviço LC 116')
                                ->default('04.01')
                                ->required()
                                ->columnSpan(1),

                            TextInput::make('codigo_municipio_ibge')
                                ->label('Município da Prestação (IBGE)')
                                ->default('1100049')
                                ->required()
                                ->columnSpan(1),

                            Textarea::make('discriminacao_servico')
                                ->label('Discriminação dos Serviços Prestados')
                                ->rows(4)
                                ->required()
                                ->placeholder('Descreva os serviços médicos/consultas prestados...')
                                ->columnSpan(2),
                        ])
                        ->columnSpan(2),
                ]),
            ]);
    }
}
