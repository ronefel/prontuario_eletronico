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
                Forms\Components\TextInput::make('nome')
                    ->label('Nome do Tratamento')
                    ->required()
                    ->maxLength(255)
                    ->prefixIcon('heroicon-o-beaker')
                    ->columnSpanFull(),
                Forms\Components\Grid::make([
                    'default' => 1,
                    'sm' => 2,
                    'md' => 2,
                    'lg' => 3,
                    'xl' => 4,
                ])
                    ->schema([
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
                        'cancelado' => 'danger',
                        default => 'gray',
                    }),
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
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'planejado' => 'Planejado',
                        'em_andamento' => 'Em Andamento',
                        'concluido' => 'Concluído',
                        'cancelado' => 'Cancelado',
                    ])
                    ->query(function (\Illuminate\Database\Eloquent\Builder $query, array $data) {
                        if (empty($data['value'])) {
                            return $query;
                        }

                        if ($data['value'] === 'cancelado') {
                            return $query->onlyTrashed();
                        }

                        $query->whereNull('deleted_at');

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
                Tables\Actions\EditAction::make()->hiddenLabel()->tooltip('Editar')
                    ->visible(function (Tratamento $record): bool {
                        return $record->deleted_at === null;
                    }),
                Tables\Actions\DeleteAction::make()->hiddenLabel()->tooltip('Cancelar'),
                Tables\Actions\RestoreAction::make()->hiddenLabel()->tooltip('Reativar'),

            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ])
            ->paginated(false)
            // deve editar apenas se o tratamento não estiver excluído
            ->recordUrl(function (Tratamento $record): ?string {
                return $record->trashed() ? null : route('filament.admin.resources.tratamentos.edit', $record);
            });
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
