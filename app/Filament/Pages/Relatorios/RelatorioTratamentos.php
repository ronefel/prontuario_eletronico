<?php

namespace App\Filament\Pages\Relatorios;

use App\Exports\TratamentosExport;
use App\Models\Tratamento;
use Filament\Forms\Components\DatePicker;
use Filament\Pages\Page;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Facades\Excel;
use Mpdf\Mpdf;

class RelatorioTratamentos extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Relatórios';

    protected static ?int $navigationSort = 301;

    protected static ?string $navigationLabel = 'Relatório de Tratamentos';

    protected static ?string $title = 'Relatório de Tratamentos';

    protected static string $view = 'filament.pages.relatorios.relatorio-tratamentos';

    public function table(Table $table): Table
    {
        return $table
            ->query(Tratamento::query()->with(['paciente', 'aplicacoes.lotes']))
            ->columns([
                TextColumn::make('paciente.nome')
                    ->label('Paciente')
                    ->sortable(),
                TextColumn::make('data_inicio')
                    ->label('Data Início')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'planejado' => 'gray',
                        'em_andamento' => 'warning',
                        'concluido' => 'success',
                        'excluido' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('aplicacoes_info')
                    ->label('Aplicações')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->html()
                    ->state(function (Tratamento $record) {
                        if ($record->aplicacoes->isEmpty()) {
                            return '<span class="text-gray-500 text-xs">Nenhuma aplicação</span>';
                        }
                        $html = '<ul class="text-xs list-disc pl-4">';
                        foreach ($record->aplicacoes as $app) {
                            $itens = $app->lotes->map(fn ($l) => $l->produto->nome)->join(', ');
                            $html .= "<li>
                                <strong>{$app->data_aplicacao?->format('d/m/Y')}</strong>
                                <span>(".ucfirst($app->status).")</span>
                                <br><span class=\"text-gray-500\">{$itens}</span>
                            </li>";
                        }
                        $html .= '</ul>';

                        return $html;
                    }),
                TextColumn::make('valor_cobrado')
                    ->label('Valor Cobrado')
                    ->money('BRL')
                    ->summarize(\Filament\Tables\Columns\Summarizers\Sum::make()
                        ->money('BRL')
                        ->label('')
                    ),
                TextColumn::make('custo_total')
                    ->label('Custo Total')
                    ->money('BRL')
                    ->summarize(\Filament\Tables\Columns\Summarizers\Summarizer::make()
                        ->label('')
                        ->money('BRL')
                        ->using(fn ($query) => Tratamento::whereIn('id', $query->clone()->pluck('tratamentos.id'))
                            ->with(['aplicacoes.lotes'])
                            ->get()
                            ->sum(fn ($record) => $record->custo_total)
                        )
                    ),
                TextColumn::make('saldo')
                    ->label('Saldo')
                    ->money('BRL')
                    ->summarize(\Filament\Tables\Columns\Summarizers\Summarizer::make()
                        ->label('')
                        ->money('BRL')
                        ->using(fn ($query) => Tratamento::whereIn('id', $query->clone()->pluck('tratamentos.id'))
                            ->with(['aplicacoes.lotes'])
                            ->get()
                            ->sum(fn ($record) => $record->saldo)
                        )
                    ),
            ])
            ->filters([
                SelectFilter::make('paciente')
                    ->label('')
                    ->searchable()
                    ->multiple()
                    ->preload()
                    ->placeholder('Pacientes')
                    ->relationship('paciente', 'nome')
                    ->columnSpan(2),
                Filter::make('data_inicio')
                    ->columns(2)
                    ->columnSpan(3)
                    ->form([
                        DatePicker::make('data_inicio_from')
                            ->label('')
                            ->placeholder('Data Início')
                            ->prefix('Data Início')
                            ->native(true),
                        DatePicker::make('data_inicio_until')
                            ->label('')
                            ->placeholder('Data Fim')
                            ->prefix('Até')
                            ->native(true),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['data_inicio_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('data_inicio', '>=', $date),
                            )
                            ->when(
                                $data['data_inicio_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('data_inicio', '<=', $date),
                            );
                    }),
            ], layout: FiltersLayout::AboveContent)->hiddenFilterIndicators()
            ->headerActions([
                Action::make('export_pdf')
                    ->label('Exportar PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->form([
                        \Filament\Forms\Components\Checkbox::make('include_applications')
                            ->label('Incluir Aplicações')
                            ->default(false),
                    ])
                    ->action(function ($livewire, array $data) {
                        $records = $livewire->getFilteredTableQuery()->get();
                        $pdf = new Mpdf([
                            'margin_left' => 10,
                            'margin_right' => 10,
                            'margin_top' => 10,
                            'margin_bottom' => 5,
                            'margin_header' => 0,
                            'margin_footer' => 5,
                            'pagenumPrefix' => 'Página ',
                            'pagenumSuffix' => ' de ',
                        ]);
                        $html = view(
                            'filament.pages.relatorios.relatorio-tratamentos-pdf',
                            [
                                'records' => $records,
                                'includeApplications' => $data['include_applications'] ?? false,
                            ]
                        )->render();
                        $pdf->setFooter('Gerado em: {DATE j/m/Y} - {PAGENO}{nbpg}');
                        $pdf->WriteHTML($html);

                        return response()->streamDownload(
                            fn () => print ($pdf->Output('', 'S')),
                            'tratamentos.pdf'
                        );
                    }),
                Action::make('export_excel')
                    ->label('Exportar Excel')
                    ->icon('heroicon-o-table-cells')
                    ->form([
                        \Filament\Forms\Components\Checkbox::make('include_applications')
                            ->label('Incluir Aplicações')
                            ->default(false),
                    ])
                    ->action(function ($livewire, array $data) {
                        $records = $livewire->getFilteredTableQuery()->get();

                        return Excel::download(
                            new TratamentosExport($records, $data['include_applications'] ?? false),
                            'tratamentos.xlsx'
                        );
                    }),
            ])
            ->actions([
            ])
            ->groups([
                Group::make('paciente.nome')
                    ->collapsible(),
            ])
            ->defaultGroup('paciente.nome')
            ->groupingSettingsHidden()
            ->paginated([100, 200, 500, 1000, 'all']);
    }
}
