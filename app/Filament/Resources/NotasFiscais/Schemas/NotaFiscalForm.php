<?php

namespace App\Filament\Resources\NotasFiscais\Schemas;

use App\Models\ConfiguracaoNotaFiscal;
use App\Models\Paciente;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class NotaFiscalForm
{
    public static function configure(Schema $schema): Schema
    {
        $configuracao = ConfiguracaoNotaFiscal::obterConfiguracaoAtiva();

        $atividadesOptions = [];
        if (! empty($configuracao?->atividades)) {
            foreach ($configuracao->atividades as $index => $act) {
                $key = $act['item_lista_servico'] ?? "04.0{$index}";
                $label = ($act['item_lista_servico'] ?? '').' - '.($act['descricao'] ?? 'Atividade');
                $atividadesOptions[$key] = $label;
            }
        } else {
            $atividadesOptions['04.01'] = '04.01 - Medicina e Biomedicina';
        }

        $cnaesOptions = [];
        if (! empty($configuracao?->cnaes)) {
            foreach ($configuracao->cnaes as $cnaeItem) {
                $cnaesOptions[$cnaeItem['codigo']] = "{$cnaeItem['codigo']} - {$cnaeItem['descricao']}";
            }
        } else {
            $cnaesOptions['8630503'] = '8630503 - Atividade médica ambulatorial';
        }

        $atividadePadrao = $configuracao?->atividade_principal['item_lista_servico'] ?? array_key_first($atividadesOptions);
        $cnaePadrao = $configuracao?->cnae_principal['codigo'] ?? array_key_first($cnaesOptions);

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
                                    ->default(fn () => request()->query('paciente_id'))
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
                                            ->formatStateUsing(fn ($state, $record) => trim("{$record->paciente?->logradouro}, {$record->paciente?->numero} - {$record->paciente?->bairro} | {$record->paciente?->cidade->nome} - {$record->paciente?->cidade->uf}"))
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                    ]),

                Section::make('Valores')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('valor_servicos')
                                    ->label('Valor do Serviço')
                                    ->numeric()
                                    ->prefix('R$')
                                    ->required(),

                                TextInput::make('aliquota_iss')
                                    ->label('Alíquota ISS (%)')
                                    ->numeric()
                                    ->default($configuracao?->aliquota_iss ?? 2.00)
                                    ->required(),

                                TextInput::make('valor_deducoes')
                                    ->label('Deduções')
                                    ->numeric()
                                    ->prefix('R$')
                                    ->default(0.00),
                            ]),
                        Section::make('Outros Impostos')
                            ->collapsed(true)
                            ->schema([
                                TextInput::make('valor_iss')
                                    ->label('Valor do ISS')
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
                            ])
                            ->secondary()
                            ->compact()
                            ->columns([
                                'sm' => 2,
                                'md' => 3,
                                'xl' => 6,
                            ]),
                    ]),

                Section::make('Informações da Prestação')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(3)->schema([
                            Select::make('item_lista_servico')
                                ->label('Atividade Municipal')
                                ->options($atividadesOptions)
                                ->default($atividadePadrao)
                                ->required(),

                            Select::make('codigo_cnae')
                                ->label('Código CNAE')
                                ->options($cnaesOptions)
                                ->default($cnaePadrao),

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
