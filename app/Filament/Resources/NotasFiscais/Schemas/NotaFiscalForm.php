<?php

namespace App\Filament\Resources\NotasFiscais\Schemas;

use App\Filament\Resources\Pacientes\Schemas\PacienteForm;
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
                                    ->relationship('paciente', 'nome')
                                    ->default(fn () => request()->query('paciente_id'))
                                    ->live()
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->editOptionForm(fn (Schema $schema) => PacienteForm::configure($schema))
                                    ->editOptionModalHeading('Editar Cadastro do Paciente'),

                                Grid::make([
                                    'sm' => 3,
                                ])->columnSpan(2)
                                    ->schema(function (Get $get): array {
                                        $idPaciente = $get('paciente_id');
                                        $paciente = $idPaciente ? Paciente::with('cidade')->find($idPaciente) : null;

                                        if (! $paciente) {
                                            return [];
                                        }

                                        $errosValidacao = $paciente->validarParaNotaFiscal();

                                        $itensHtml = array_map(fn ($erro) => '<li style="margin-bottom: 0.25rem;">❌ '.e($erro).'</li>', $errosValidacao);
                                        $listaErros = implode('', $itensHtml);

                                        $statusValidacao = ! empty($errosValidacao)
                                            ? '<div style="background-color: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.4); color: #dc2626; border-radius: 0.5rem; padding: 0.75rem 1rem;">
                                                    <strong style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem;">
                                                        ⚠️ Dados faltantes ou incorretos para emissão da Nota Fiscal:
                                                    </strong>
                                                    <ul style="margin-top: 0.5rem; margin-bottom: 0.5rem; padding-left: 1.25rem; font-size: 0.875rem; list-style-type: none;">
                                                        '.$listaErros.'
                                                    </ul>
                                                    <small style="color: #b91c1c;">Clique no ícone de lápis ao lado do campo do Paciente para atualizar estes dados.</small>
                                               </div>'
                                            : '<div style="background-color: rgba(34, 197, 94, 0.1); border: 1px solid rgba(34, 197, 94, 0.4); color: #15803d; border-radius: 0.5rem; padding: 0.75rem 1rem; font-size: 0.875rem;">
                                                    <strong>✓ Cadastro do paciente completo e válido para emissão da NFS-e.</strong>
                                               </div>';

                                        // Exibe informações do paciente selecionado e o resultado da validação
                                        return [
                                            TextEntry::make('paciente.cpf')
                                                ->label('CPF')
                                                ->state($paciente->cpf ?: 'Não informado'),

                                            TextEntry::make('paciente.email')
                                                ->label('E-mail')
                                                ->state($paciente->email ?: 'Não informado'),

                                            TextEntry::make('paciente.celular')
                                                ->label('Celular')
                                                ->state($paciente->celularFormatado() ?: $paciente->celular ?: 'Não informado'),

                                            TextEntry::make('paciente.logradouro')
                                                ->label('Endereço')
                                                ->state(trim("{$paciente->logradouro}, {$paciente->numero} - {$paciente->bairro} | {$paciente->cidade?->nome} - {$paciente->cidade?->uf}"))
                                                ->columnSpanFull(),

                                            TextEntry::make('status_validacao_paciente')
                                                ->label('Validação para NFS-e')
                                                ->html()
                                                ->state($statusValidacao)
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
                        Grid::make(2)->schema([
                            Select::make('item_lista_servico')
                                ->label('Atividade Municipal')
                                ->options($atividadesOptions)
                                ->default($atividadePadrao)
                                ->required(),

                            Select::make('codigo_cnae')
                                ->label('Código CNAE')
                                ->options($cnaesOptions)
                                ->default($cnaePadrao),

                            Textarea::make('discriminacao_servico')
                                ->label('Discriminação dos Serviços Prestados')
                                ->rows(4)
                                ->minLength(11)
                                ->required()
                                ->default($configuracao?->discriminacao_servico)
                                ->placeholder('Descreva os serviços médicos/consultas prestados...')
                                ->columnSpanFull(),
                        ]),
                    ]),
            ]);
    }
}
