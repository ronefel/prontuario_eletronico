<?php

namespace App\Filament\Widgets;

use App\Models\Lote;
use Carbon\Carbon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\HtmlString;

class ValidadeProximaWidget extends TableWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected ?Collection $data = null;

    protected static function getData(): Collection
    {
        return Cache::remember('validade_proxima_widget', 60, fn () => Lote::query()
            ->with('produto')
            ->withSum('movimentacoes', 'quantidade')
            ->where('status', 'ativo')
            ->whereNotNull('data_validade')
            ->where('data_validade', '<=', Carbon::now()->addDays(30))
            ->whereRaw('(
                SELECT COALESCE(SUM(quantidade), 0)
                FROM movimentacoes
                WHERE movimentacoes.lote_id = lotes.id
                AND movimentacoes.deleted_at IS NULL
            ) > 0')
            ->get());
    }

    public static function canView(): bool
    {
        return ! self::getData()->isEmpty();
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading(new HtmlString(Blade::render('<div class="flex items-center gap-2"><x-heroicon-o-cube class="h-5 w-5" /> Lotes Vencidos ou com Validade Próxima</div>')))
            ->query(
                $this->getData()->toQuery()->with('produto')->withSum('movimentacoes', 'quantidade')
            )
            ->columns([
                TextColumn::make('numero_lote')
                    ->label('Lote'),
                TextColumn::make('produto.nome')
                    ->label('Produto')
                    ->limit(80)
                    ->tooltip(fn (string $state): ?string => mb_strlen($state) > 80 ? $state : null),
                TextColumn::make('quantidade_atual')
                    ->label('Qtd Atual'),
                TextColumn::make('data_validade')
                    ->label('Validade')
                    ->date('d/m/Y')
                    ->color(function ($record) {
                        $date = Carbon::parse($record->getRawOriginal('data_validade'), Auth::user()->timezone);

                        if ($date->endOfDay()->isPast()) {
                            return 'danger';
                        }
                        if ($date->addDays(-30)->startOfDay()->isPast()) {
                            return 'warning';
                        }
                        if ($date->isFuture()) {
                            return 'success';
                        }
                    }),
                TextColumn::make('dias_restantes')
                    ->label('Faltam')
                    ->getStateUsing(function ($record) {
                        $validade = Carbon::parse($record->getRawOriginal('data_validade'), Auth::user()->timezone)->startOfDay();
                        $hoje = Carbon::now(Auth::user()->timezone)->startOfDay();

                        if ($validade->lessThan($hoje)) {
                            return 'Vencido';
                        }

                        $dias = (int) $hoje->diffInDays($validade);

                        return $dias === 0 ? 'Vence Hoje' : $dias.' dias';
                    }),
            ])
            ->recordUrl(fn ($record) => route('filament.admin.resources.lotes.edit', $record))
            ->emptyStateHeading('Nenhum lote vencido ou com validade próxima')
            ->emptyStateDescription('Nenhum lote está vencido ou vence nos próximos 30 dias.')
            ->defaultSort('data_validade')
            ->paginated(false);
    }
}
