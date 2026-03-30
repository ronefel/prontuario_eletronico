<?php

namespace App\Filament\Resources\Movimentacoes\Schemas;

use App\Models\Lote;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class MovimentacaoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('tipo')
                    ->label('Tipo')
                    ->options([
                        'entrada' => 'Entrada',
                        'saida' => 'Saída',
                        'ajuste' => 'Ajuste',
                        'transferencia' => 'Transferência',
                    ])
                    ->required()
                    ->helperText('Tipo da movimentação. "Ajuste" é usado para correções manuais.'),
                Select::make('produto_id')
                    ->label('Produto')
                    ->relationship('produto', 'nome')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live(),
                Select::make('lote_id')
                    ->label('Lote')
                    ->relationship(
                        name: 'lote',
                        titleAttribute: 'numero_lote',
                        modifyQueryUsing: fn (Builder $query, Get $get) => $query
                            ->where('produto_id', $get('produto_id'))
                    )
                    ->getOptionLabelFromRecordUsing(fn (Lote $record) => $record->display_name)
                    ->searchable() // apenas ativa a busca
                    ->preload()
                    ->native(false)
                    ->allowHtml()
                    ->required()
                    ->disabled(fn (Get $get) => blank($get('produto_id'))) // desativa se não tiver produto
                    ->placeholder(fn (Get $get) => $get('produto_id')
                        ? 'Selecione uma opção'
                        : 'Selecione um produto primeiro'
                    ),
                TextInput::make('quantidade')
                    ->label('Quantidade')
                    ->numeric()
                    ->required()
                    ->rule(fn (Get $get) => $get('tipo') === 'entrada' ? 'min:1' : 'not_in:0') // Entrada: impede negativos e zero; outros: impede zero
                    ->validationMessages([
                        'min' => 'A quantidade não pode ser zero ou negativa para entradas.',
                        'not_in' => 'A quantidade não pode ser zero.',
                    ])
                    ->helperText('Para "Ajuste", use valores positivos para somar e negativos para subtrair.'),
                DateTimePicker::make('data_movimentacao')
                    ->label('Data da Movimentação')
                    ->default(now())
                    ->seconds(false)
                    ->required(),
                TextInput::make('documento')
                    ->label('Documento Relacionado (ex: Nota Fiscal)')
                    ->nullable(),
                Textarea::make('motivo')
                    ->label('Motivo')
                    ->nullable()
                    ->columnSpanFull(),
            ]);
    }
}
