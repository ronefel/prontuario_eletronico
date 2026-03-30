<?php

namespace App\Filament\Pages\Relatorios;

use App\Exports\TratamentosExport;
use App\Models\Tratamento;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Mpdf\Mpdf;
use UnitEnum;

class RelatorioTratamentos extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.pages.relatorios.relatorio-tratamentos';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static string|UnitEnum|null $navigationGroup = 'Relatórios';

    protected static ?int $navigationSort = 301;

    protected static ?string $navigationLabel = 'Relatório de Tratamentos';

    protected static ?string $title = 'Relatório de Tratamentos';

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
                        $html = '<ul class="text-xs list-disc pl-4 space-y-2">';
                        foreach ($record->aplicacoes as $app) {
                            $html .= "<li>
                                <strong>{$app->data_aplicacao?->format('d/m/Y')}</strong>
                                <span>(".ucfirst($app->status).")</span>
                                <ul class='list-circle pl-4 text-gray-500'>";
                            foreach ($app->lotes as $lote) {
                                $html .= '<li>- '.$lote->pivot->quantidade.'x '.Str::limit($lote->produto->nome, 40).'</li>';
                            }
                            $html .= '</ul></li>';
                        }
                        $html .= '</ul>';

                        return $html;
                    }),
                TextColumn::make('valor_cobrado')
                    ->label('Valor Cobrado')
                    ->money('BRL')
                    ->summarize(Sum::make()
                        ->money('BRL')
                        ->label('')
                    ),
                TextColumn::make('custo_total')
                    ->label('Custo Total')
                    ->money('BRL')
                    ->summarize(Summarizer::make()
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
                    ->summarize(Summarizer::make()
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
                Filter::make('paciente')
                    ->columnSpan(2)
                    ->schema([
                        Select::make('paciente')
                            ->hiddenLabel()
                            ->searchable()
                            ->multiple()
                            ->preload()
                            ->placeholder('Selecione os Pacientes')
                            ->relationship('paciente', 'nome'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['paciente'],
                            fn (Builder $query, $pacientes): Builder => $query->whereIn('paciente_id', $pacientes),
                        );
                    }),
                Filter::make('data_inicio')
                    ->columns(2)
                    ->columnSpan(3)
                    ->schema([
                        DatePicker::make('data_inicio_from')
                            ->hiddenLabel()
                            ->placeholder('Data Início')
                            ->prefix('De')
                            ->native(true),
                        DatePicker::make('data_inicio_until')
                            ->hiddenLabel()
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
            ], layout: FiltersLayout::AboveContent)
            ->filtersFormColumns(5)
            ->hiddenFilterIndicators()
            ->headerActions([
                Action::make('export_pdf')
                    ->label('Exportar PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->schema([
                        Checkbox::make('include_applications')
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
                            'orientation' => $data['include_applications'] ? 'L' : 'P',
                            'pagenumPrefix' => 'Página ',
                            'pagenumSuffix' => ' de ',
                        ]);
                        $html = view('filament.pages.relatorios.relatorio-tratamentos-pdf', [
                            'records' => $records,
                            'includeApplications' => $data['include_applications'] ?? false,
                        ])->render();
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
                    ->schema([
                        Checkbox::make('include_applications')
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
            ->groups([
                Group::make('paciente.nome')
                    ->collapsible(),
            ])
            ->defaultGroup('paciente.nome')
            ->groupingSettingsHidden()
            ->paginated([100, 200, 500, 1000, 'all']);
    }
}
