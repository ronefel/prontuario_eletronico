<?php

namespace App\Filament\Resources\NotasFiscais;

use App\Filament\Resources\NotasFiscais\Pages\CreateNotaFiscal;
use App\Filament\Resources\NotasFiscais\Pages\EditNotaFiscal;
use App\Filament\Resources\NotasFiscais\Pages\ListNotasFiscais;
use App\Filament\Resources\NotasFiscais\Pages\ViewNotaFiscal;
use App\Filament\Resources\NotasFiscais\Schemas\NotaFiscalForm;
use App\Filament\Resources\NotasFiscais\Tables\NotasFiscaisTable;
use App\Models\NotaFiscal;
use BackedEnum;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class NotaFiscalResource extends Resource
{
    protected static ?string $model = NotaFiscal::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentCheck;

    protected static string|UnitEnum|null $navigationGroup = 'Financeiro';

    protected static ?int $navigationSort = 301;

    protected static ?string $modelLabel = 'Nota Fiscal';

    protected static ?string $pluralModelLabel = 'Notas Fiscais';

    public static function canEdit(Model $record): bool
    {
        /** @var NotaFiscal $record */
        return $record->status === 'rascunho';
    }

    public static function canDelete(Model $record): bool
    {
        /** @var NotaFiscal $record */
        return $record->status === 'rascunho';
    }

    public static function form(Schema $schema): Schema
    {
        return NotaFiscalForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return NotasFiscaisTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identificação e Status da Nota Fiscal')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(4)->schema([
                            TextEntry::make('status')
                                ->label('Status')
                                ->badge()
                                ->formatStateUsing(fn (string $state): string => match ($state) {
                                    'rascunho' => 'Rascunho',
                                    'processando' => 'Processando',
                                    'autorizada' => 'Autorizada',
                                    'cancelada' => 'Cancelada',
                                    'rejeitada' => 'Rejeitada',
                                    default => ucfirst($state),
                                })
                                ->color(fn (string $state): string => match ($state) {
                                    'rascunho' => 'gray',
                                    'processando' => 'warning',
                                    'autorizada' => 'success',
                                    'cancelada', 'rejeitada' => 'danger',
                                    default => 'info',
                                }),

                            TextEntry::make('numero_rps')
                                ->label('Número do RPS')
                                ->weight(FontWeight::Bold),

                            TextEntry::make('serie_rps')
                                ->label('Série do RPS'),

                            TextEntry::make('data_emissao_rps')
                                ->label('Data de Emissão do RPS')
                                ->dateTime('d/m/Y H:i:s', timezone: auth()->user()?->timezone),

                            TextEntry::make('numero_nfse')
                                ->label('Número da NFS-e')
                                ->placeholder('Não gerado (Rascunho)'),

                            TextEntry::make('codigo_verificacao')
                                ->label('Código de Verificação')
                                ->placeholder('Pendente'),

                            TextEntry::make('data_emissao_nfse')
                                ->label('Data de Emissão da NFS-e')
                                ->dateTime('d/m/Y H:i:s', timezone: auth()->user()?->timezone)
                                ->placeholder('Pendente'),
                        ]),
                    ]),

                Section::make('Tomador do Serviço (Paciente)')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(4)->schema([
                            TextEntry::make('paciente.nome')
                                ->label('Nome do Paciente')
                                ->weight(FontWeight::Bold),

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

                Section::make('Detalhamento Financeiro e Impostos')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(5)->schema([
                            TextEntry::make('valor_servicos')
                                ->label('Valor do Serviço')
                                ->money('BRL')
                                ->weight(FontWeight::Bold),

                            TextEntry::make('aliquota_iss')
                                ->label('Alíquota ISS')
                                ->formatStateUsing(fn ($state) => number_format((float) $state, 2, ',', '.').'%'),

                            TextEntry::make('valor_iss')
                                ->label('Valor do ISS')
                                ->money('BRL'),

                            TextEntry::make('valor_deducoes')
                                ->label('Deduções')
                                ->money('BRL'),

                            TextEntry::make('valor_pis')
                                ->label('PIS')
                                ->money('BRL'),

                            TextEntry::make('valor_cofins')
                                ->label('COFINS')
                                ->money('BRL'),

                            TextEntry::make('valor_inss')
                                ->label('INSS')
                                ->money('BRL'),

                            TextEntry::make('valor_ir')
                                ->label('IR')
                                ->money('BRL'),

                            TextEntry::make('valor_csll')
                                ->label('CSLL')
                                ->money('BRL'),
                        ]),
                    ]),

                Section::make('Discriminação do Serviço Prestado')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(2)->schema([
                            TextEntry::make('item_lista_servico')
                                ->label('Item LC 116'),

                            TextEntry::make('codigo_municipio_ibge')
                                ->label('Código IBGE Município'),

                            TextEntry::make('discriminacao_servico')
                                ->label('Discriminação dos Serviços')
                                ->columnSpanFull(),
                        ]),
                    ]),

                Section::make('Retorno / Erros da Prefeitura')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(2)->schema([
                            TextEntry::make('codigo_erro')
                                ->label('Código do Erro')
                                ->placeholder('Nenhum erro registrado'),

                            TextEntry::make('mensagem_erro')
                                ->label('Mensagem de Erro')
                                ->placeholder('Sem erros')
                                ->columnSpanFull(),
                        ]),
                    ])
                    ->visible(fn ($record) => ! empty($record->mensagem_erro)),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListNotasFiscais::route('/'),
            'create' => CreateNotaFiscal::route('/create'),
            'view' => ViewNotaFiscal::route('/{record}'),
            'edit' => EditNotaFiscal::route('/{record}/edit'),
        ];
    }
}
