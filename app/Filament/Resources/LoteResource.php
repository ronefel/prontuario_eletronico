<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LoteResource\Pages;
use App\Models\Lote;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LoteResource extends Resource
{
    protected static ?string $model = Lote::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationLabel = 'Lotes';

    protected static ?string $navigationGroup = 'Estoque';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('produto_id')
                    ->relationship('produto', 'nome')
                    ->required(),
                Forms\Components\Select::make('fornecedor_id')
                    ->relationship('fornecedor', 'nome')
                    ->nullable(),
                Forms\Components\TextInput::make('numero_lote')
                    ->required()
                    ->unique(ignoreRecord: true),
                Forms\Components\DatePicker::make('data_fabricacao')
                    ->nullable(),
                Forms\Components\DatePicker::make('data_validade')
                    ->nullable(),
                Forms\Components\TextInput::make('quantidade_inicial')
                    ->numeric()
                    ->required()
                    ->minValue(0),
                Forms\Components\TextInput::make('valor_unitario')
                    ->numeric()
                    ->prefix('R$'),
                Forms\Components\Select::make('local_id')
                    ->relationship('local', 'nome')
                    ->required(),
                Forms\Components\Select::make('status')
                    ->options([
                        'ativo' => 'Ativo',
                        'expirado' => 'Expirado',
                        'bloqueado' => 'Bloqueado',
                    ])
                    ->default('ativo'),
                Forms\Components\TextInput::make('documento')
                    ->label('Documento Relacionado (ex: Nota Fiscal)')
                    ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('produto.nome')
                    ->searchable(),
                Tables\Columns\TextColumn::make('fornecedor.nome')
                    ->searchable(),
                Tables\Columns\TextColumn::make('numero_lote')
                    ->searchable(),
                Tables\Columns\TextColumn::make('data_validade')
                    ->date('d/m/Y')
                    ->color(fn ($record) => $record->data_validade && $record->data_validade->isPast() ? 'danger' : 'success'),
                Tables\Columns\TextColumn::make('quantidade_atual')
                    ->sortable()
                    ->getStateUsing(fn ($record) => $record->quantidade_atual),
                Tables\Columns\TextColumn::make('local.nome'),
                Tables\Columns\TextColumn::make('status'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('produto')
                    ->relationship('produto', 'nome'),
                Tables\Filters\SelectFilter::make('fornecedor')
                    ->relationship('fornecedor', 'nome'),
                Tables\Filters\SelectFilter::make('local')
                    ->relationship('local', 'nome'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLotes::route('/'),
            'create' => Pages\CreateLote::route('/create'),
            'edit' => Pages\EditLote::route('/{record}/edit'),
        ];
    }
}
