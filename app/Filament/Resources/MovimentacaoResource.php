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
use Illuminate\Support\Facades\Auth;

class MovimentacaoResource extends Resource
{
    protected static ?string $model = Movimentacao::class;

    protected static ?string $modelLabel = 'Movimentação';

    protected static ?string $pluralModelLabel = 'Movimentações';

    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static ?string $navigationLabel = 'Movimentações';

    protected static ?string $navigationGroup = 'Estoque';

    protected static ?int $navigationSort = 207;

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
                Forms\Components\TextInput::make('documento')
                    ->label('Documento Relacionado (ex: Nota Fiscal)')
                    ->nullable(),
                Forms\Components\Textarea::make('motivo')
                    ->nullable()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('#')->sortable(),
                Tables\Columns\TextColumn::make('tipo'),
                Tables\Columns\TextColumn::make('produto.nome')->searchable(),
                Tables\Columns\TextColumn::make('lote.numero_lote')->searchable(),
                Tables\Columns\TextColumn::make('quantidade')
                    ->color(fn ($record) => $record->quantidade < 0 ? 'danger' : 'success'),
                Tables\Columns\TextColumn::make('data_movimentacao')->dateTime('d/m/Y H:i', Auth::user()->timezone)->sortable(),
                Tables\Columns\TextColumn::make('motivo')->html()->words(11)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();

                        if (count(explode(' ', $state)) <= $column->getWordLimit()) {
                            return null;
                        }

                        // Only render the tooltip if the column content exceeds the length limit.
                        return $state;
                    }),
            ])
            ->defaultSort('id', 'desc')
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
                Tables\Actions\EditAction::make()
                    ->hiddenLabel()
                    ->tooltip('Editar')
                    ->visible(fn ($record) => $record->is_manual),
                Tables\Actions\DeleteAction::make()
                    ->hiddenLabel()
                    ->tooltip('Excluir')
                    ->visible(fn ($record) => $record->is_manual),
            ])
            ->bulkActions([
                // Tables\Actions\DeleteBulkAction::make(),
            ])
            ->recordUrl(fn (Movimentacao $record): ?string => $record->is_manual ? Pages\EditMovimentacao::getUrl(['record' => $record]) : null);
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
