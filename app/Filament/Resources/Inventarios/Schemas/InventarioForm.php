<?php

namespace App\Filament\Resources\Inventarios\Schemas;

use App\Models\Local;
use App\Models\Produto;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

class InventarioForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('data_inventario')
                    ->label('Data do Inventário')
                    ->default(today())
                    ->required(),
                ToggleButtons::make('status')
                    ->label('Status')
                    ->options([
                        'pendente' => 'Pendente',
                        'aprovado' => 'Aprovado',
                    ])
                    ->default('pendente')
                    ->inline()
                    ->disabled(),
                TextInput::make('user_id')
                    ->label('Usuário')
                    ->default(fn () => Auth::user()->id)
                    ->formatStateUsing(function ($state, $record) {
                        if ($record && $record->user) {
                            return $record->user->name;
                        }

                        return Auth::user()->name;
                    })
                    ->readOnly()
                    ->dehydrateStateUsing(fn ($state, $context) => Auth::user()->id),
                Select::make('tipo')
                    ->label('Tipo')
                    ->options([
                        'completo' => 'Completo',
                        'por_local' => 'Por Local',
                        'por_produto' => 'Por Produto',
                    ])
                    ->required()
                    ->reactive()
                    ->disabled(fn ($context) => $context === 'edit')
                    ->helperText(new HtmlString('
                        <div class="space-y-1 text-xs">
                            <p><strong>Completo:</strong> Inventário de todos os lotes.</p>
                            <p><strong>Por Local:</strong> Inventário dos lotes dos locais selecionados.</p>
                            <p><strong>Por Produto:</strong> Inventário dos lotes dos produtos selecionados.</p>
                        </div>
                    ')),
                Select::make('produto_id')
                    ->label('Produtos')
                    ->options(Produto::pluck('nome', 'id')->toArray())
                    ->searchable()
                    ->preload()
                    ->multiple()
                    ->visible(fn ($get) => $get('tipo') === 'por_produto')
                    ->requiredIf('tipo', 'por_produto')
                    ->reactive()
                    ->hidden(fn ($get, $context) => $context === 'edit'),
                Select::make('local_id')
                    ->label('Locais')
                    ->options(Local::pluck('nome', 'id')->toArray())
                    ->default(fn () => Local::count() === 1 ? [Local::first()->id] : null)
                    ->searchable()
                    ->multiple()
                    ->visible(fn ($get) => $get('tipo') === 'por_local')
                    ->requiredIf('tipo', 'por_local')
                    ->reactive()
                    ->hidden(fn ($get, $context) => $context === 'edit'),
            ]);
    }
}
