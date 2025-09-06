<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MovimentacaoResource\Pages;
use App\Models\Lote;
use App\Models\Movimentacao;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MovimentacaoResource extends Resource
{
    protected static ?string $model = Movimentacao::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static ?string $navigationLabel = 'Movimentações';

    protected static ?string $navigationGroup = 'Estoque';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('tipo')
                    ->options([
                        'entrada' => 'Entrada',
                        'saida' => 'Saída',
                        'ajuste' => 'Ajuste',
                        'transferencia' => 'Transferência',
                    ])
                    ->required(),
                Forms\Components\Select::make('produto_id')
                    ->relationship('produto', 'nome')
                    ->required()
                    ->live(),
                Forms\Components\Select::make('lote_id')
                    ->options(fn (Get $get) => $get('produto_id')
                        ? Lote::where('produto_id', $get('produto_id'))
                            ->get()
                            ->mapWithKeys(fn ($lote) => [$lote->id => $lote->display_name])
                        : collect()
                    )
                    ->native(false)
                    ->allowHtml()
                    ->required()
                    ->placeholder(fn (Get $get) => $get('produto_id') ? 'Selecione uma opção' : 'Selecione um produto primeiro'),
                Forms\Components\TextInput::make('quantidade')
                    ->numeric()
                    ->required()
                    ->rules([
                        fn (Get $get) => $get('tipo') === 'entrada' ? 'min:1' : 'not_in:0', // Entrada: impede negativos e zero; outros: impede zero
                    ])
                    ->validationMessages([
                        'min' => 'A quantidade não pode ser zero ou negativa para entradas.',
                        'not_in' => 'A quantidade não pode ser zero.',
                    ]),
                Forms\Components\DateTimePicker::make('data_movimentacao')
                    ->default(now())
                    ->seconds(false)
                    ->required(),
                Forms\Components\Textarea::make('motivo')
                    ->nullable(),
                Forms\Components\TextInput::make('documento')
                    ->label('Documento Relacionado (ex: Nota Fiscal)')
                    ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tipo')->sortable(),
                Tables\Columns\TextColumn::make('produto.nome')->searchable(),
                Tables\Columns\TextColumn::make('lote.numero_lote')->searchable(),
                Tables\Columns\TextColumn::make('quantidade')
                    ->color(fn ($record) => $record->quantidade < 0 ? 'danger' : 'success'),
                Tables\Columns\TextColumn::make('data_movimentacao')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('motivo')->limit(50),
                Tables\Columns\TextColumn::make('user.name'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('tipo')
                    ->options([
                        'entrada' => 'Entrada',
                        'saida' => 'Saída',
                        'ajuste' => 'Ajuste',
                        'transferencia' => 'Transferência',
                    ]),
                Tables\Filters\SelectFilter::make('produto')
                    ->relationship('produto', 'nome'),
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
            'index' => Pages\ListMovimentacoes::route('/'),
            'create' => Pages\CreateMovimentacao::route('/create'),
            'edit' => Pages\EditMovimentacao::route('/{record}/edit'),
        ];
    }
}
