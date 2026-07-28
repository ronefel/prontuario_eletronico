<?php

namespace App\Filament\Resources\NotasFiscais\Schemas;

use App\Models\ConfiguracaoNotaFiscal;
use App\Models\Paciente;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class NotaFiscalForm
{
    public static function configure(Schema $schema): Schema
    {
        $configuracao = ConfiguracaoNotaFiscal::obterConfiguracaoAtiva();

        return $schema
            ->components([
                Section::make('Tomador do Serviço (Paciente)')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make([
                            'lg' => 2,
                        ])
                            ->schema([
                                Select::make('paciente_id')
                                    ->label('Paciente / Tomador')
                                    ->options(Paciente::all()->pluck('nome', 'id'))
                                    ->default(fn() => request()->query('paciente_id'))
                                    ->searchable()
                                    ->required(),
                                Grid::make([
                                    'sm' => 3,
                                ])
                                    ->schema([
                                        TextEntry::make('paciente.cpf')
                                            ->label('CPF'),

                                        TextEntry::make('paciente.email')
                                            ->label('E-mail')
                                            ->placeholder('Não informado'),

                                        TextEntry::make('paciente.celular')
                                            ->label('Celular')
                                            ->placeholder('Não informado'),

                                        TextEntry::make('paciente.logradouro')
                                            ->label('Endereço')
                                            ->formatStateUsing(fn($state, $record) => trim("{$record->paciente?->logradouro}, {$record->paciente?->numero} - {$record->paciente?->bairro} | {$record->paciente?->cidade->nome} - {$record->paciente?->cidade->uf}"))
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                    ]),

                Section::make('Detalhamento dos Valores do Serviço')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make([
                            'sm' => 2,
                            'md' => 3,
                            'lg' => 5,
                        ])
                            ->schema([
                                TextInput::make('valor_servicos')
                                    ->label('Valor dos Serviços')
                                    ->numeric()
                                    ->prefix('R$')
                                    ->required(),

                                TextInput::make('aliquota_iss')
                                    ->label('Alíquota ISS (%)')
                                    ->numeric()
                                    ->default($configuracao?->aliquota_iss ?? 2.00)
                                    ->required(),

                                TextInput::make('valor_iss')
                                    ->label('Valor do ISS')
                                    ->numeric()
                                    ->prefix('R$')
                                    ->default(0.00),

                                TextInput::make('valor_deducoes')
                                    ->label('Deduções')
                                    ->numeric()
                                    ->prefix('R$')
                                    ->default(0.00),

                                TextInput::make('valor_pis')
                                    ->label('PIS')
                                    ->numeric()
                                    ->prefix('R$')
                                    ->default(0.00),

                                TextInput::make('valor_cofins')
                                    ->label('COFINS')
                                    ->numeric()
                                    ->prefix('R$')
                                    ->default(0.00),

                                TextInput::make('valor_inss')
                                    ->label('INSS')
                                    ->numeric()
                                    ->prefix('R$')
                                    ->default(0.00),

                                TextInput::make('valor_ir')
                                    ->label('IR')
                                    ->numeric()
                                    ->prefix('R$')
                                    ->default(0.00),

                                TextInput::make('valor_csll')
                                    ->label('CSLL')
                                    ->numeric()
                                    ->prefix('R$')
                                    ->default(0.00),
                            ]),
                    ]),

                Section::make('Informações da Prestação')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('item_lista_servico')
                                ->label('Item Lista Serviço LC 116')
                                ->default($configuracao?->item_lista_servico ?? '04.01')
                                ->required(),

                            TextInput::make('codigo_municipio_ibge')
                                ->label('Município da Prestação (IBGE)')
                                ->default($configuracao?->codigo_municipio_ibge ?? '1100049')
                                ->required(),

                            Textarea::make('discriminacao_servico')
                                ->label('Discriminação dos Serviços Prestados')
                                ->rows(4)
                                ->required()
                                ->placeholder('Descreva os serviços médicos/consultas prestados...')
                                ->columnSpanFull(),
                        ]),
                    ]),
            ]);
    }
}
