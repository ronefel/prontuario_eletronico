<?php

namespace App\Filament\Widgets;

use App\Models\Produto;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\HtmlString;

class EstoqueBaixoWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected static ?string $pollingInterval = null;

    protected ?Collection $data = null;

    protected static function getData(): Collection
    {
        return Cache::remember('estoque_baixo_widget', 300, fn () => Produto::withEstoqueBaixo()->get());
    }

    public static function canView(): bool
    {
        $data = self::getData();

        return ! $data->isEmpty();
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading(new HtmlString(Blade::render('<div class="flex items-center gap-2"><x-heroicon-o-rectangle-stack class="h-5 w-5" /> Produtos com Estoque Baixo</div>')))
            ->query(
                $this->getData()->toQuery()->withSum('movimentacoes', 'quantidade')
            )
            ->columns([
                Tables\Columns\TextColumn::make('nome')
                    ->label('Produto')
                    ->limit(40)
                    ->tooltip(fn (string $state): ?string => mb_strlen($state) > 40 ? $state : null),
                Tables\Columns\TextColumn::make('quantidade_atual')
                    ->label('Qtd Atual')
                    ->color('danger'),
                Tables\Columns\TextColumn::make('estoque_minimo')
                    ->label('Qtd Mínima'),
            ])
            ->recordUrl(fn ($record) => route('filament.admin.resources.produtos.edit', $record))
            ->emptyStateHeading('Nenhum produto com estoque baixo')
            ->emptyStateDescription('Todos os produtos estão acima do estoque mínimo.')
            ->defaultSort('nome')
            ->paginated(false);
    }
}
