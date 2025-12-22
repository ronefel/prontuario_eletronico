<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LoteResource\Pages;
use App\Models\Lote;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class LoteResource extends Resource
{
    protected static ?string $model = Lote::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationLabel = 'Lotes';

    protected static ?string $navigationGroup = 'Estoque';

    protected static ?int $navigationSort = 205;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('produto_id')
                    ->label('Produto')
                    ->relationship('produto', 'nome')
                    ->searchable()
                    ->preload()
                    ->createOptionForm(ProdutoResource::formFields())
                    ->required(),
                Forms\Components\TextInput::make('numero_lote')
                    ->label('Número do Lote')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->hint('Campo obrigatório e único'),
                Forms\Components\DatePicker::make('data_fabricacao')
                    ->label('Data de Fabricação')
                    ->helperText('Data de fabricação do lote (opcional).')
                    ->nullable(),
                Forms\Components\DatePicker::make('data_validade')
                    ->label('Data de Validade')
                    ->helperText('Data de validade. O sistema avisará quando estiver próximo do vencimento.')
                    ->nullable(),
                Forms\Components\TextInput::make('quantidade_inicial')
                    ->label('Quantidade Inicial')
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->helperText('Quantidade total recebida.'),
                Forms\Components\TextInput::make('valor_unitario')
                    ->label('Valor Unitário')
                    ->prefix('R$')
                    ->helperText('Valor de custo por unidade.'),
                Forms\Components\Select::make('local_id')
                    ->label('Local')
                    ->relationship('local', 'nome')
                    ->default(fn () => \App\Models\Local::count() === 1 ? \App\Models\Local::first()->id : null)
                    ->required(),
                Forms\Components\Select::make('fornecedor_id')
                    ->label('Fornecedor')
                    ->relationship('fornecedor', 'nome')
                    ->searchable()
                    ->preload()
                    ->createOptionForm(FornecedorResource::formFields())
                    ->nullable(),
                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        'ativo' => 'Ativo',
                        'expirado' => 'Expirado',
                        'bloqueado' => 'Bloqueado',
                    ])
                    ->default('ativo')
                    ->helperText('Status do lote.'),
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
                    ->color(function ($record) {
                        $date = Carbon::parse($record->getRawOriginal('data_validade'), Auth::user()->timezone);

                        if ($date->endOfDay()->isPast()) {
                            return 'danger';
                        }
                        if ($date->addDays(-30)->startOfDay()->isPast()) {
                            return 'warning';
                        }
                        if ($date->isFuture()) {
                            return 'success';
                        }
                    }),
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
