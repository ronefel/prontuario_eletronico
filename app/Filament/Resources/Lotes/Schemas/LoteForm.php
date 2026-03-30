<?php

namespace App\Filament\Resources\Lotes\Schemas;

use App\Filament\Resources\Fornecedors\Schemas\FornecedorForm;
use App\Filament\Resources\Produtos\Schemas\ProdutoForm;
use App\Models\Local;
use App\Models\Produto;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class LoteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('produto_id')
                    ->label('Produto')
                    ->relationship('produto', 'nome')
                    ->searchable()
                    ->preload()
                    ->createOptionForm(ProdutoForm::getComponents())
                    ->editOptionForm(ProdutoForm::getComponents())
                    ->required()
                    ->live()
                    ->afterStateUpdated(function (Set $set, ?string $state) {
                        if (! $state) {
                            return;
                        }

                        $produto = Produto::find($state);

                        if ($produto) {
                            $set('valor_unitario', $produto->valor_unitario_referencia);
                            $set('fornecedor_id', $produto->fornecedor_id);
                        }
                    }),
                TextInput::make('numero_lote')
                    ->label('Número do Lote')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->hint('Campo obrigatório e único'),
                DatePicker::make('data_fabricacao')
                    ->label('Data de Fabricação')
                    ->helperText('Data de fabricação do lote (opcional).')
                    ->nullable()
                    ->live()
                    ->afterStateUpdated(function (Set $set, Get $get, ?string $state) {
                        if ($state && ! $get('data_validade')) {
                            $set('data_validade', Carbon::parse($state)->addYear()->format('Y-m-d'));
                        }
                    }),
                DatePicker::make('data_validade')
                    ->label('Data de Validade')
                    ->helperText('Data de validade. O sistema avisará quando estiver próximo do vencimento.')
                    ->nullable()
                    ->live()
                    ->afterStateUpdated(function (Set $set, Get $get, ?string $state) {
                        if ($state && ! $get('data_fabricacao')) {
                            $set('data_fabricacao', Carbon::parse($state)->subYear()->format('Y-m-d'));
                        }
                    }),
                TextInput::make('quantidade_inicial')
                    ->label('Quantidade Inicial')
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->helperText('Quantidade total recebida.'),
                TextInput::make('valor_unitario')
                    ->label('Valor Unitário')
                    ->numeric()
                    ->prefix('R$')
                    ->helperText('Valor de custo por unidade.'),
                Select::make('local_id')
                    ->label('Local')
                    ->relationship('local', 'nome')
                    ->default(fn () => Local::count() === 1 ? Local::first()->id : null)
                    ->required(),
                Select::make('fornecedor_id')
                    ->label('Fornecedor')
                    ->relationship('fornecedor', 'nome')
                    ->searchable()
                    ->preload()
                    ->createOptionForm(FornecedorForm::getComponents())
                    ->editOptionForm(FornecedorForm::getComponents())
                    ->nullable(),
                Select::make('status')
                    ->label('Status')
                    ->options([
                        'ativo' => 'Ativo',
                        'expirado' => 'Expirado',
                        'bloqueado' => 'Bloqueado',
                    ])
                    ->default('ativo')
                    ->helperText('Status do lote.'),
                TextInput::make('documento')
                    ->label('Documento Relacionado (ex: Nota Fiscal)')
                    ->nullable(),
            ]);
    }
}
