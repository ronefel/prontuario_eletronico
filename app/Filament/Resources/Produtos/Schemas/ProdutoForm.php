<?php

namespace App\Filament\Resources\Produtos\Schemas;

use App\Filament\Resources\Categorias\Schemas\CategoriaForm;
use App\Filament\Resources\Fornecedors\Schemas\FornecedorForm;
use App\Models\Produto;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProdutoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components(self::getComponents());
    }

    public static function getComponents(): array
    {
        return [
            TextInput::make('nome')
                ->label('Nome')
                ->required(),
            TextInput::make('unidade_medida')
                ->label('Unidade de Medida')
                ->default('unidade')
                ->helperText('Ex: Unidade, Frasco, Ampola, Caixa.'),
            TextInput::make('valor_unitario_referencia')
                ->label('Valor Unitário de Referência')
                ->numeric()
                ->prefix('R$'),
            TextInput::make('estoque_minimo')
                ->label('Estoque Mínimo')
                ->numeric()
                ->default(10)
                ->helperText('O sistema alertará quando o estoque total estiver abaixo deste valor.'),
            Select::make('categoria_id')
                ->label('Categoria')
                ->relationship('categoria', 'nome')
                ->searchable()
                ->preload()
                ->createOptionForm(CategoriaForm::getComponents())
                ->editOptionForm(CategoriaForm::getComponents())
                ->required(),
            Select::make('fornecedor_id')
                ->label('Fornecedor')
                ->relationship('fornecedor', 'nome')
                ->searchable()
                ->preload()
                ->createOptionForm(FornecedorForm::getComponents())
                ->editOptionForm(FornecedorForm::getComponents())
                ->nullable(),
            Textarea::make('descricao')
                ->label('Descrição')
                ->columnSpanFull()
                ->nullable(),
            Section::make('Informações de Estoque')
                ->collapsible()
                ->collapsed()
                ->schema([
                    TextEntry::make('quantidade_atual_estoque')
                        ->label('Quantidade Atual em Estoque')
                        ->state(fn (?Produto $record): string => $record ? (string) $record->lotes->sum('quantidade_atual') : '0'),
                    TextEntry::make('valor_total_estoque')
                        ->label('Valor Total em Estoque')
                        ->state(function (?Produto $record): string {
                            if (! $record) {
                                return 'R$ 0,00';
                            }
                            $quantidade = $record->movimentacoes_sum_quantidade ?? $record->lotes->sum('quantidade_atual');
                            $valorUnitario = (float) $record->valor_unitario_referencia;

                            return 'R$ '.number_format($quantidade * $valorUnitario, 2, ',', '.');
                        }),
                    Repeater::make('lotes')
                        ->relationship()
                        ->schema([
                            TextEntry::make('numero_lote')
                                ->label('Lote'),
                            TextEntry::make('quantidade_atual')
                                ->label('Qtd Atual'),
                            TextEntry::make('data_validade')
                                ->label('Validade')
                                ->state(fn ($record) => $record->data_validade?->format('d/m/Y') ?? '-'),
                            TextEntry::make('local_nome')
                                ->label('Local')
                                ->state(fn ($record) => $record->local->nome ?? '-'),
                        ])
                        ->addable(false)
                        ->deletable(false)
                        ->reorderable(false)
                        ->columns(4)
                        ->label('Lotes desse produto'),
                ])
                ->columnSpanFull()
                ->columns(2)
                ->visible(fn (?Produto $record) => $record !== null),
        ];
    }
}
