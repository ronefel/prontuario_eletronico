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
                    ->maxLength(255),
                Forms\Components\Textarea::make('observacao')
                    ->label('Observações Clínicas')
                    ->placeholder('Detalhes do protocolo, posologia, justificativa...')
                    ->rows(3)
                    ->columnSpanFull(),
                Forms\Components\DatePicker::make('data_inicio')
                    ->label('Data de Início')
                    ->default(now())
                    ->required(),
                Forms\Components\DatePicker::make('data_fim')
                    ->label('Data de Fim')
                    ->default(now())
                    ->nullable(),
                Forms\Components\Select::make('status')
                    ->options([
                        'planejado' => 'Planejado',
                        'em_andamento' => 'Em Andamento',
                        'concluido' => 'Concluído',
                        'cancelado' => 'Cancelado',
                    ])
                    ->default('planejado')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nome')
                    ->label('Tratamento')
                    ->searchable(),
                Tables\Columns\TextColumn::make('data_inicio')
                    ->label('Data de Início')
                    ->date(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'planejado' => 'gray',
                        'em_andamento' => 'warning',
                        'concluido' => 'success',
                        'cancelado' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                // Adicione filtros se necessário
            ])
            ->actions([
                Tables\Actions\EditAction::make()->hiddenLabel(),
                Tables\Actions\DeleteAction::make()->hiddenLabel(),
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
