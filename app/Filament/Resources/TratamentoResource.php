<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TratamentoResource\Pages;
use App\Filament\Resources\TratamentoResource\RelationManagers\AplicacoesRelationManager;
use App\Models\Tratamento;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TratamentoResource extends Resource
{
    protected static ?string $model = Tratamento::class;

    protected static bool $isGloballySearchable = false;

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function getBreadcrumb(): string
    {
        return '';
    }

    public static function getGlobalSearchLabel(): ?string
    {
        return null;
    }

    public static function getLabel(): ?string
    {
        return ' ';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Hidden::make('paciente_id')
                    ->default(fn ($livewire) => $livewire->paciente?->id),

                Forms\Components\Grid::make([
                    'default' => 1,
                    'sm' => 2,
                    'md' => 2,
                    'lg' => 4,
                    'xl' => 4,
                ])
                    ->schema([

                        Forms\Components\TextInput::make('nome')
                            ->label('Nome do Tratamento')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(2),
                        Forms\Components\DatePicker::make('data_inicio')
                            ->label('Data de Início')
                            ->default(now())
                            ->required()
                            ->prefixIcon('heroicon-o-calendar'),
                        Forms\Components\DatePicker::make('data_fim')
                            ->label('Data de Fim')
                            ->default(now())
                            ->nullable()
                            ->prefixIcon('heroicon-o-calendar'),
                    ]),

                Forms\Components\Textarea::make('observacao')
                    ->label('Observações Clínicas')
                    ->placeholder('Detalhes do protocolo, posologia, justificativa...')
                    ->rows(3)
                    ->columnSpanFull(),

                Forms\Components\Section::make('Financeiro')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Forms\Components\TextInput::make('valor_cobrado')
                            ->label('Valor Cobrado')
                            ->numeric()
                            ->prefix('R$')
                            ->inputMode('decimal')
                            ->live()
                            ->helperText(fn (?Tratamento $record) => 'Valor sugerido: '.($record ? number_format($record->custo_total * 12, 2, ',', '.') : '0,00')),

                        Forms\Components\Placeholder::make('custo_total')
                            ->label('Custo Total')
                            ->content(fn (?Tratamento $record): string => $record ? 'R$ '.number_format($record->custo_total, 2, ',', '.') : 'R$ 0,00'),

                        Forms\Components\Placeholder::make('saldo')
                            ->label('Saldo')
                            ->content(function (Forms\Get $get, ?Tratamento $record): string {
                                $cobrado = $get('valor_cobrado') ?? 0;
                                if (is_string($cobrado)) {
                                    $cobrado = (float) str_replace(',', '.', $cobrado);
                                }
                                $custo = $record->custo_total ?? 0;

                                return 'R$ '.number_format((float) $cobrado - (float) $custo, 2, ',', '.');
                            }),

                        Forms\Components\Placeholder::make('porcentagem_ganho')
                            ->label('')
                            ->content(function (Forms\Get $get, ?Tratamento $record): string {
                                $cobrado = $get('valor_cobrado') ?? 0;
                                if (is_string($cobrado)) {
                                    $cobrado = (float) str_replace(',', '.', $cobrado);
                                }
                                $custo = $record->custo_total ?? 0;
                                $multiplicador = $custo > 0 ? ((float) $cobrado / (float) $custo) : ($cobrado > 0 ? 1 : 0);

                                return number_format($multiplicador, 2, ',', '.').'x';
                            }),
                    ])
                    ->columns(4),
            ]);
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                \Illuminate\Database\Eloquent\SoftDeletingScope::class,
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('#')
                    ->sortable(),
                Tables\Columns\TextColumn::make('nome')
                    ->label('Tratamento')
                    ->searchable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('data_inicio')
                    ->label('Data de Início')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('data_fim')
                    ->label('Data de Fim')
                    ->date('d/m/Y'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'planejado' => 'gray',
                        'em_andamento' => 'warning',
                        'concluido' => 'success',
                        'excluido' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('progresso')
                    ->label('Progresso')
                    ->tooltip('Aplicada/Total')
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('observacao')
                    ->label('Observações')
                    ->limit(30)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();

                        if (strlen($state) <= $column->getCharacterLimit()) {
                            return null;
                        }

                        // Only render the tooltip if the column content exceeds the length limit.
                        return $state;
                    }),
            ])
            ->defaultSort('data_inicio')
            ->filters([
                Tables\Filters\TrashedFilter::make()->native(false),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'planejado' => 'Planejado',
                        'em_andamento' => 'Em Andamento',
                        'concluido' => 'Concluído',
                    ])
                    ->query(function (\Illuminate\Database\Eloquent\Builder $query, array $data) {
                        if (empty($data['value'])) {
                            return $query;
                        }

                        return match ($data['value']) {
                            'planejado' => $query->where(function ($q) {
                                $q->doesntHave('aplicacoes')
                                    ->orWhereDoesntHave('aplicacoes', fn ($sq) => $sq->where('status', 'aplicada'));
                            }),
                            'concluido' => $query->whereHas('aplicacoes', fn ($q) => $q->where('status', 'aplicada'))
                                ->whereDoesntHave('aplicacoes', fn ($q) => $q->where('status', '!=', 'aplicada')),
                            'em_andamento' => $query->whereHas('aplicacoes', fn ($q) => $q->where('status', 'aplicada'))
                                ->whereHas('aplicacoes', fn ($q) => $q->where('status', '!=', 'aplicada')),
                            default => $query,
                        };
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->hiddenLabel()
                    ->tooltip('Editar'),
                Tables\Actions\DeleteAction::make()
                    ->hiddenLabel()
                    ->tooltip('Excluir')
                    ->visible(fn (Tratamento $record) => $record->status === 'planejado'),
                Tables\Actions\RestoreAction::make()
                    ->hiddenLabel()
                    ->tooltip('Restaurar'),
                Tables\Actions\ForceDeleteAction::make()
                    ->hiddenLabel()
                    ->tooltip('Excluir Definitivamente'),

            ])
            ->paginated(false);
    }

    public static function getRelations(): array
    {
        return [
            AplicacoesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTratamentos::route('/'),
            'list' => Pages\ListTratamentos::route('/{pacienteId}'),
            'create' => Pages\CreateTratamento::route('/create/{pacienteId}'),
            'edit' => Pages\EditTratamento::route('/{record}/edit'),
        ];
    }
}
