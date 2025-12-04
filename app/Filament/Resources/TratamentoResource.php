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
                    'md' => 3,
                    'lg' => 4,
                    'xl' => 5,
                ])
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                'planejado' => 'Planejado',
                                'em_andamento' => 'Em Andamento',
                                'concluido' => 'Concluído',
                                'cancelado' => 'Cancelado',
                            ])
                            ->default('planejado')
                            ->required()
                            ->native(false)
                            ->prefixIcon('heroicon-o-information-circle'),

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

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nome')
                    ->label('Tratamento')
                    ->searchable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('data_inicio')
                    ->label('Data de Início')
                    ->date('d/m/Y')
                    ->sortable(),
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
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Adicione filtros se necessário
            ])
            ->actions([
                Tables\Actions\EditAction::make()->hiddenLabel()->tooltip('Editar'),
                Tables\Actions\DeleteAction::make()->hiddenLabel()->tooltip('Excluir'),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ]);
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
