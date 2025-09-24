<?php

namespace App\Filament\Widgets;

use App\Models\Lote;
use Carbon\Carbon;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class ValidadeProximaWidget extends BaseWidget
{
    protected static ?int $sort = 5;

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
            ->heading('Lotes Vencidos ou com Validade Próxima')
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
                    ->color('danger'),
                Tables\Columns\TextColumn::make('dias_restantes')
                    ->label('Dias Restantes')
                    ->getStateUsing(function ($record) {
                        $validade = Carbon::parse($record->data_validade)->startOfDay();
                        $hoje = Carbon::now(Auth::user()->timezone)->startOfDay();
                        if ($validade->lessThanOrEqualTo($hoje)) {
                            return 'Vencido';
                        }

                        return $hoje->diffInDays($validade);
                    }),
            ])
            ->recordUrl(fn ($record) => route('filament.admin.resources.lotes.edit', $record))
            ->emptyStateHeading('Nenhum lote vencido ou com validade próxima')
            ->emptyStateDescription('Nenhum lote está vencido ou vence nos próximos 30 dias.')
            ->defaultSort('data_validade')
            ->paginated(false);
    }
}
