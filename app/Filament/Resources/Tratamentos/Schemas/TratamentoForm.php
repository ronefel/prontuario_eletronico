<?php

namespace App\Filament\Resources\Tratamentos\Schemas;

use App\Models\Tratamento;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class TratamentoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('paciente_id')
                    ->default(fn ($livewire) => $livewire->paciente?->id),
                TextInput::make('nome')
                    ->label('Nome do Tratamento')
                    ->required()
                    ->maxLength(255),
                Grid::make([
                    'default' => 1,
                    'sm' => 2,
                ])
                    ->schema([

                        DatePicker::make('data_inicio')
                            ->label('Data de Início')
                            ->default(now())
                            ->required()
                            ->prefixIcon('heroicon-o-calendar'),
                        DatePicker::make('data_fim')
                            ->label('Data de Fim')
                            ->default(now())
                            ->nullable()
                            ->prefixIcon('heroicon-o-calendar'),
                    ]),

                Textarea::make('observacao')
                    ->label('Observações Clínicas')
                    ->placeholder('Detalhes do protocolo, posologia, justificativa...')
                    ->rows(3)
                    ->columnSpanFull(),

                Section::make('Financeiro')
                    ->columnSpanFull()
                    ->collapsible()
                    ->columns(4)
                    ->collapsed()
                    ->schema([
                        TextInput::make('valor_cobrado')
                            ->label('Valor Cobrado')
                            ->numeric()
                            ->prefix('R$')
                            ->inputMode('decimal')
                            ->live()
                            ->helperText(fn (?Tratamento $record) => 'Valor sugerido: '.($record ? number_format($record->custo_total * 12, 2, ',', '.') : '0,00')),

                        TextEntry::make('custo_total')
                            ->label('Custo Total')
                            ->state(fn (?Tratamento $record): string => $record ? 'R$ '.number_format($record->custo_total, 2, ',', '.') : 'R$ 0,00'),

                        TextEntry::make('saldo')
                            ->label('Saldo')
                            ->state(function (Get $get, ?Tratamento $record): string {
                                $cobrado = $get('valor_cobrado') ?? 0;
                                if (is_string($cobrado)) {
                                    $cobrado = (float) str_replace(',', '.', $cobrado);
                                }
                                $custo = $record->custo_total ?? 0;

                                return 'R$ '.number_format((float) $cobrado - (float) $custo, 2, ',', '.');
                            }),

                        TextEntry::make('porcentagem_ganho')
                            ->label('')
                            ->state(function (Get $get, ?Tratamento $record): string {
                                $cobrado = $get('valor_cobrado') ?? 0;
                                if (is_string($cobrado)) {
                                    $cobrado = (float) str_replace(',', '.', $cobrado);
                                }
                                $custo = $record->custo_total ?? 0;
                                $multiplicador = $custo > 0 ? ((float) $cobrado / (float) $custo) : ($cobrado > 0 ? 1 : 0);

                                return number_format($multiplicador, 2, ',', '.').'x';
                            }),
                    ]),
            ]);
    }
}
