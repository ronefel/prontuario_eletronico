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
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class NotaFiscalForm
{
    public static function configure(Schema $schema): Schema
    {
        $configuracao = ConfiguracaoNotaFiscal::obterConfiguracaoAtiva();

        $atividadesOptions = [];
        if (! empty($configuracao?->atividades)) {
            foreach ($configuracao->atividades as $codigo => $descricao) {
                if (is_array($descricao)) {
                    $chave = $descricao['item_lista_servico'] ?? $descricao['codigo_tributacao_municipio'] ?? (string) $codigo;
                    $rotulo = $descricao['descricao'] ?? '';
                    $atividadesOptions[$chave] = "{$chave} - {$rotulo}";
                } else {
                    $atividadesOptions[$codigo] = "{$codigo} - {$descricao}";
                }
            }
        }

        $cnaesOptions = [];
        if (! empty($configuracao?->cnaes)) {
            foreach ($configuracao->cnaes as $codigo => $descricao) {
                if (is_array($descricao)) {
                    $chave = $descricao['codigo'] ?? (string) $codigo;
                    $rotulo = $descricao['descricao'] ?? '';
                    $cnaesOptions[$chave] = "{$chave} - {$rotulo}";
                } else {
                    $cnaesOptions[$codigo] = "{$codigo} - {$descricao}";
                }
            }
        }

        $atividadePadrao = $configuracao?->atividade_principal['item_lista_servico'] ?? array_key_first($atividadesOptions);
        $cnaePadrao = $configuracao?->cnae_principal['codigo'] ?? array_key_first($cnaesOptions);

        return $schema
            ->components([
                Section::make('Tomador do Serviço (Paciente)')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make([
                            'lg' => 3,
                        ])
                            ->schema([
                                Select::make('paciente_id')
                                    ->label('Paciente / Tomador')
                                    ->options(Paciente::all()->pluck('nome', 'id'))
                                    ->default(fn () => request()->query('paciente_id'))
                                    ->live()
                                    ->searchable()
                                    ->required(),
                                Grid::make([
                                    'sm' => 3,
                                ])->columnSpan(2)
                                    ->schema(function (Get $get): array {
                                        $idPaciente = $get('paciente_id');
                                        $paciente = $idPaciente ? Paciente::with('cidade')->find($idPaciente) : null;

                                        // Exibe informações do paciente selecionado
                                        return [
                                            TextEntry::make('paciente.cpf')
                                                ->label('CPF')
                                                ->state($paciente?->cpf),

                                            TextEntry::make('paciente.email')
                                                ->label('E-mail')
                                                ->state($paciente?->email),

                                            TextEntry::make('paciente.celular')
                                                ->label('Celular')
                                                ->state($paciente?->celularFormatado() ?: $paciente?->celular),

                                            TextEntry::make('paciente.logradouro')
                                                ->label('Endereço')
                                                ->state($paciente ? trim("{$paciente?->logradouro}, {$paciente?->numero} - {$paciente?->bairro} | {$paciente?->cidade?->nome} - {$paciente?->cidade?->uf}") : null)
                                                ->columnSpanFull(),
                                        ];
                                    }),
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
