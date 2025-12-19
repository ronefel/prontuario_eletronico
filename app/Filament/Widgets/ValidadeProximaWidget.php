<?php

namespace App\Filament\Widgets;

use App\Models\Lote;
use Carbon\Carbon;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\HtmlString;

class ValidadeProximaWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected ?Collection $data = null;

    protected static function getData(): Collection
    {
        return Cache::remember('validade_proxima_widget', 60, fn () => Lote::query()
            ->with('produto')
            ->where('status', 'ativo')
            ->whereNotNull('data_validade')
            ->where('data_validade', '<=', Carbon::now()->addDays(30))
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
                $this->getData()->toQuery()->with('produto')
            )
            ->columns([
                Tables\Columns\TextColumn::make('numero_lote')
                    ->label('Lote'),
                Tables\Columns\TextColumn::make('produto.nome')
                    ->label('Produto'),
                Tables\Columns\TextColumn::make('data_validade')
                    ->label('Data de Validade')
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
                Tables\Columns\TextColumn::make('dias_restantes')
                    ->label('Dias Restantes')
                    ->getStateUsing(function ($record) {
                        $validade = Carbon::parse($record->getRawOriginal('data_validade'), Auth::user()->timezone)->startOfDay();
                        $hoje = Carbon::now(Auth::user()->timezone)->startOfDay();

                        if ($validade->lessThan($hoje)) {
                            return 'Vencido';
                        }

                        $dias = (int) $hoje->diffInDays($validade);

                        return $dias === 0 ? 'Vence Hoje' : $dias;
                    }),
            ])
            ->recordUrl(fn ($record) => route('filament.admin.resources.lotes.edit', $record))
            ->emptyStateHeading('Nenhum lote vencido ou com validade próxima')
            ->emptyStateDescription('Nenhum lote está vencido ou vence nos próximos 30 dias.')
            ->defaultSort('data_validade')
            ->paginated(false);
    }
}
