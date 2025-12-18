<?php

namespace App\Filament\Pages\Relatorios;

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
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RelatorioTratamentos extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Relatórios';

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
                TextColumn::make('valor_cobrado')
                    ->label('Valor Cobrado')
                    ->money('BRL'),
                TextColumn::make('custo_total')
                    ->label('Custo Total')
                    ->money('BRL'),
                TextColumn::make('saldo')
                    ->label('Saldo')
                    ->money('BRL'),
            ])
            ->filters([
                SelectFilter::make('paciente')
                    ->label('')
                    ->searchable()
                    ->multiple()
                    ->preload()
                    ->placeholder('Pacientes')
                    ->relationship('paciente', 'nome'),
                Filter::make('data_inicio')
                    ->columns(2)
                    ->columnSpan(2)
                    ->form([
                        DatePicker::make('data_inicio_from')
                            ->label('')
                            ->placeholder('Data Início')
                            ->native(false),
                        DatePicker::make('data_inicio_until')
                            ->label('')
                            ->placeholder('Data Fim')
                            ->native(false),
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
                    ->action(function ($livewire) {
                        $records = $livewire->getFilteredTableQuery()->get();
                        $pdf = new \Mpdf\Mpdf;
                        $html = view('filament.pages.relatorios.relatorio-tratamentos-pdf', ['records' => $records])->render();
                        $pdf->WriteHTML($html);

                        return response()->streamDownload(
                            fn () => print ($pdf->Output('', 'S')),
                            'tratamentos.pdf'
                        );
                    }),
                Action::make('export_excel')
                    ->label('Exportar Excel')
                    ->icon('heroicon-o-table-cells')
                    ->action(function ($livewire) {
                        $records = $livewire->getFilteredTableQuery()->get();

                        return \Maatwebsite\Excel\Facades\Excel::download(
                            new \App\Exports\TratamentosExport($records),
                            'tratamentos.xlsx'
                        );
                    }),
            ])
            ->actions([
                Action::make('view_applications')
                    ->label('Ver Aplicações')
                    ->icon('heroicon-m-eye')
                    ->modalContent(fn (Tratamento $record) => view('filament.pages.relatorios.partials.applications-list', ['record' => $record]))
                    ->modalSubmitAction(false)
                    ->modalCancelAction(false),
            ])
            ->defaultGroup('paciente.nome');
    }
}
