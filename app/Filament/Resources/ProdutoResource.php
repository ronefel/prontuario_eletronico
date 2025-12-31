<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProdutoResource\Pages;
use App\Models\Produto;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProdutoResource extends Resource
{
    protected static ?string $model = Produto::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Produtos';

    protected static ?string $navigationGroup = 'Estoque';

    protected static ?int $navigationSort = 204;

    public static function form(Form $form): Form
    {
        return $form
            ->schema(self::formFields());
    }

    public static function formFields(): array
    {
        return [
            Forms\Components\TextInput::make('nome')
                ->label('Nome')
                ->required(),
            Forms\Components\Textarea::make('descricao')
                ->label('Descrição')
                ->nullable(),
            Forms\Components\TextInput::make('unidade_medida')
                ->label('Unidade de Medida')
                ->default('unidade')
                ->helperText('Ex: Unidade, Frasco, Ampola, Caixa.'),
            Forms\Components\TextInput::make('valor_unitario_referencia')
                ->label('Valor Unitário de Referência')
                ->numeric()
                ->prefix('R$'),
            Forms\Components\TextInput::make('estoque_minimo')
                ->label('Estoque Mínimo')
                ->numeric()
                ->default(10)
                ->helperText('O sistema alertará quando o estoque total estiver abaixo deste valor.'),
            Forms\Components\Select::make('categoria_id')
                ->label('Categoria')
                ->relationship('categoria', 'nome')
                ->searchable()
                ->preload()
                ->createOptionForm(CategoriaResource::formFields())
                ->required(),
            Forms\Components\Select::make('fornecedor_id')
                ->label('Fornecedor')
                ->relationship('fornecedor', 'nome')
                ->searchable()
                ->preload()
                ->createOptionForm(FornecedorResource::formFields())
                ->nullable(),
            Forms\Components\Section::make('Informações de Estoque')
                ->schema([
                    Forms\Components\Placeholder::make('quantidade_atual_estoque')
                        ->label('Quantidade Atual em Estoque')
                        ->content(fn (?Produto $record): string => $record ? (string) $record->lotes->sum('quantidade_atual') : '0'),
                    Forms\Components\Placeholder::make('valor_total_estoque')
                        ->label('Valor Total em Estoque')
                        ->content(function (?Produto $record): string {
                            if (! $record) {
                                return 'R$ 0,00';
                            }
                            $quantidade = $record->lotes->sum('quantidade_atual');
                            $valorUnitario = (float) $record->valor_unitario_referencia;

                            return 'R$ '.number_format($quantidade * $valorUnitario, 2, ',', '.');
                        }),
                ])
                ->columns(2)
                ->visible(fn (?Produto $record) => $record !== null),
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nome')->searchable(),
                Tables\Columns\TextColumn::make('categoria.nome'),
                Tables\Columns\TextColumn::make('estoque_total')
                    ->getStateUsing(fn ($record) => $record->lotes->sum('quantidade_atual'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('valor_unitario_referencia')->money('BRL'),
                Tables\Columns\TextColumn::make('estoque_minimo')
                    ->color(fn ($record) => $record->lotes->sum('quantidade_atual') < $record->estoque_minimo ? 'danger' : 'success'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('categoria')->relationship('categoria', 'nome'),
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
            'index' => Pages\ListProdutos::route('/'),
            'create' => Pages\CreateProduto::route('/create'),
            'edit' => Pages\EditProduto::route('/{record}/edit'),
        ];
    }
}
