<?php

namespace App\Filament\Resources\Inventarios\Pages;

use App\Filament\Resources\Inventarios\InventarioResource;
use App\Models\Inventario;
use App\Models\InventarioLote;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;

class ViewInventario extends ViewRecord
{
    protected static string $resource = InventarioResource::class;

    protected string $view = 'filament.resources.inventarios.pages.contagem-inventario';

    public ?Inventario $proximo;

    public ?Inventario $anterior;

    public array $contagens = [];

    public ?string $pesquisa = '';

    public function mount($record): void
    {
        parent::mount($record);
        $this->carregarContagens();
    }

    public function getTitle(): string
    {
        return 'Inventário #'.$this->record->id;
    }

    public function carregarContagens(): void
    {
        $this->contagens = InventarioLote::with(['lote.produto'])
            ->join('lotes', 'inventario_lote.lote_id', '=', 'lotes.id')
            ->join('produtos', 'lotes.produto_id', '=', 'produtos.id')
            ->where('inventario_id', $this->record->id)
            ->orderBy('produtos.nome')
            ->select('inventario_lote.*')
            ->get()
            ->mapWithKeys(fn ($item) => [
                $item->id => [
                    'id' => $item->id,
                    'numero_lote' => $item->lote->numero_lote,
                    'produto' => $item->lote->produto->nome,
                    'vencimento' => $item->lote->data_validade?->format('d/m/y') ?? '-',
                    'registrada' => $item->quantidade_registrada,
                    'contada' => $item->quantidade_contada,
                    'motivo' => $item->motivo_discrepancia,
                ],
            ])
            ->toArray();
    }

    public function salvarContagem($id, $valor): void
    {
        if ($this->record->status !== 'pendente') {
            return;
        }

        /** @var InventarioLote|null $item */
        $item = InventarioLote::find($id);
        if ($item) {
            $item->quantidade_contada = (int) $valor;

            if ($item->quantidade_contada == $item->quantidade_registrada) {
                $item->motivo_discrepancia = null;
            }

            $item->save();

            $this->contagens[$id]['contada'] = $item->quantidade_contada;
            $this->contagens[$id]['motivo'] = $item->motivo_discrepancia;
        }
    }

    public function salvarMotivo($id, $motivo): void
    {
        if ($this->record->status !== 'pendente') {
            return;
        }

        /** @var InventarioLote|null $item */
        $item = InventarioLote::find($id);
        if ($item) {
            $item->motivo_discrepancia = $motivo;
            $item->save();
            $this->contagens[$id]['motivo'] = $motivo;
        }
    }

    public function getItensFiltradosProperty(): array
    {
        if (empty($this->pesquisa)) {
            return $this->contagens;
        }

        $termo = mb_strtolower($this->pesquisa);

        return array_filter($this->contagens, function ($item) use ($termo) {
            return str_contains(mb_strtolower($item['numero_lote']), $termo) ||
                   str_contains(mb_strtolower($item['produto']), $termo);
        });
    }

    public function finalizarContagem(): void
    {
        $this->redirect(static::getResource()::getUrl('index'));
    }

    /**
     * Busca o registro anterior e o próximo
     */
    protected function montarNavegacao(): void
    {
        $this->proximo = Inventario::where('id', '>', $this->record->id)
            ->orderBy('id')
            ->first();

        $this->anterior = Inventario::where('id', '<', $this->record->id)
            ->orderBy('id', 'desc')
            ->first();
    }

    /**
     * Verifica se existe próximo registro
     */
    protected function temProximo(): bool
    {
        return $this->proximo !== null;
    }

    /**
     * Verifica se existe registro anterior
     */
    protected function temAnterior(): bool
    {
        return $this->anterior !== null;
    }

    protected function getHeaderActions(): array
    {
        $this->montarNavegacao();

        return [
            // Botão Imprimir para Conferência
            Actions\Action::make('conferencia')
                ->label('Imprimir')
                ->color('info')
                ->visible(fn () => $this->record->status === 'pendente')
                ->icon('heroicon-o-printer')
                ->url(fn () => route('inventario.print', ['id' => $this->record->id]))
                ->openUrlInNewTab(),

            // Ação de Aprovar
            Actions\Action::make('aprovar')
                ->label('Aprovar')
                ->visible(fn () => $this->record->status === 'pendente')
                ->color('success')
                ->icon('heroicon-s-check')
                ->requiresConfirmation()
                ->modalHeading('Aprovar Inventário')
                ->modalDescription('Isso irá ajustar o estoque conforme as discrepâncias encontradas. Deseja continuar?')
                ->action(function () {
                    $record = $this->record;

                    foreach ($record->inventarioLotes as $item) {
                        $discrepancia = $item->quantidade_contada - $item->quantidade_registrada;

                        if ($discrepancia != 0) {
                            $item->lote->produto->movimentacoes()->create([
                                'tipo' => 'ajuste',
                                'lote_id' => $item->lote_id,
                                'quantidade' => $discrepancia,
                                'data_movimentacao' => now(),
                                'motivo' => 'Ajuste via inventário #'.$record->id,
                                'user_id' => Auth::user()->id,
                                'valor_unitario' => $item->lote->valor_unitario,
                            ]);
                            $item->save();
                        }
                    }

                    $record->status = 'aprovado';
                    $record->save();

                    Notification::make()
                        ->title('Inventário aprovado e estoque ajustado!')
                        ->success()
                        ->send();

                    $this->redirect(static::getResource()::getUrl('view', ['record' => $record]));
                }),

            // Botão Voltar
            Actions\Action::make('voltar')
                ->label('Voltar')
                ->outlined()
                ->icon('heroicon-o-arrow-uturn-left')
                ->url(static::getResource()::getUrl('index'))
                ->tooltip('Voltar para a lista de inventários'),

            // Botão Anterior
            Actions\Action::make('anterior')
                ->label('')
                ->outlined()
                ->icon('heroicon-o-arrow-left')
                ->size('xs')
                ->disabled(! $this->temAnterior())
                ->url(fn () => $this->temAnterior()
                    ? static::getResource()::getUrl('view', ['record' => $this->anterior])
                    : null
                )
                ->tooltip(fn () => $this->temAnterior()
                    ? 'Inventário #'.$this->anterior->id
                    : 'Nenhum inventário anterior'
                ),

            // Botão Próximo
            Actions\Action::make('proximo')
                ->label('')
                ->outlined()
                ->icon('heroicon-o-arrow-right')
                ->size('xs')
                ->disabled(! $this->temProximo())
                ->url(fn () => $this->temProximo()
                    ? static::getResource()::getUrl('view', ['record' => $this->proximo])
                    : null
                )
                ->tooltip(fn () => $this->temProximo()
                    ? 'Inventário #'.$this->proximo->id
                    : 'Nenhum inventário posterior'
                ),
        ];
    }
}
