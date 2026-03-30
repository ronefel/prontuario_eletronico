@php
    use Filament\Actions\Action;
    use Filament\Support\Enums\Alignment;
    use Illuminate\View\ComponentAttributeBag;

    $vistaEnvoltórioCampo = $getFieldWrapperView();

    $itens = $getItems();

    $açãoAdicionar = $getAction($getAddActionName());
    $alinhamentoAçãoAdicionar = $getAddActionAlignment();
    $açãoClonar = $getAction($getCloneActionName());
    $açãoExcluir = $getAction($getDeleteActionName());
    $açãoMoverParaBaixo = $getAction($getMoveDownActionName());
    $açãoMoverParaCima = $getAction($getMoveUpActionName());
    $açãoReordenar = $getAction($getReorderActionName());
    $açõesExtrasItem = $getExtraItemActions();

    $éAdicionável = $isAddable();
    $éClonável = $isCloneable();
    $éExcluível = $isDeletable();
    $éReordenávelComBotões = $isReorderableWithButtons();
    $éReordenávelComArrastarESoltar = $isReorderableWithDragAndDrop();

    $chave = $getKey();
    $caminhoEstado = $getStatePath();
@endphp

<x-dynamic-component :component="$vistaEnvoltórioCampo" :field="$field">
    <div {{ $attributes->merge($getExtraAttributes(), escape: false)->class(['fi-fo-simple-repeater']) }}>
        @if (count($itens))
            <ul x-sortable
                {{ (new ComponentAttributeBag)->grid($getGridColumns())->merge(
                        [
                            'data-sortable-animation-duration' => $getReorderAnimationDuration(),
                            'x-on:end.stop' =>
                                '$wire.mountAction(\'reorder\', { items: $event.target.sortable.toArray() }, { schemaComponent: \'' .
                                $chave .
                                '\' })',
                        ],
                        escape: false,
                    )->class(['fi-fo-simple-repeater-items']) }}>
                @foreach ($itens as $chaveItem => $item)
                    @php
                        $açõesExtrasItemVisíveis = array_filter(
                            $açõesExtrasItem,
                            fn(Action $ação): bool => $ação(['item' => $chaveItem])->isVisible(),
                        );
                        $açãoClonarInstância = $açãoClonar(['item' => $chaveItem]);
                        $açãoClonarVisível = $éClonável && $açãoClonarInstância->isVisible();
                        $açãoExcluirInstância = $açãoExcluir(['item' => $chaveItem]);
                        $açãoExcluirVisível = $éExcluível && $açãoExcluirInstância->isVisible();
                        $açãoMoverParaBaixoInstância = $açãoMoverParaBaixo(['item' => $chaveItem])->disabled(
                            $loop->last,
                        );
                        $açãoMoverParaBaixoVisível =
                            $éReordenávelComBotões && $açãoMoverParaBaixoInstância->isVisible();
                        $açãoMoverParaCimaInstância = $açãoMoverParaCima(['item' => $chaveItem])->disabled(
                            $loop->first,
                        );
                        $açãoMoverParaCimaVisível = $éReordenávelComBotões && $açãoMoverParaCimaInstância->isVisible();
                        $açãoReordenarVisível = $éReordenávelComArrastarESoltar && $açãoReordenar->isVisible();
                    @endphp

                    <li wire:key="{{ $item->getLivewireKey() }}.item" x-sortable-item="{{ $chaveItem }}"
                        class="fi-fo-simple-repeater-item flex justify-start gap-x-3" style="align-items: start">
                        <div class="fi-fo-simple-repeater-item-content flex-1" style="align-items: baseline">
                            {{ $item }}
                        </div>

                        @if (
                            $açãoReordenarVisível ||
                                $açãoMoverParaCimaVisível ||
                                $açãoMoverParaBaixoVisível ||
                                $açãoClonarVisível ||
                                $açãoExcluirVisível ||
                                $açõesExtrasItemVisíveis)
                            <ul class="fi-fo-simple-repeater-item-actions flex items-center gap-x-1"
                                style="transform: translate(0%, 50%);">
                                @if ($açãoReordenarVisível)
                                    <li x-on:click.stop>
                                        {{ $açãoReordenar->extraAttributes(['x-sortable-handle' => true], merge: true) }}
                                    </li>
                                @endif

                                @if ($açãoMoverParaCimaVisível || $açãoMoverParaBaixoVisível)
                                    <li x-on:click.stop>
                                        {{ $açãoMoverParaCimaInstância }}
                                    </li>

                                    <li x-on:click.stop>
                                        {{ $açãoMoverParaBaixoInstância }}
                                    </li>
                                @endif

                                @foreach ($açõesExtrasItemVisíveis as $açãoExtraItem)
                                    <li x-on:click.stop>
                                        {{ $açãoExtraItem(['item' => $chaveItem]) }}
                                    </li>
                                @endforeach

                                @if ($açãoClonarVisível)
                                    <li x-on:click.stop>
                                        {{ $açãoClonarInstância }}
                                    </li>
                                @endif

                                @if ($açãoExcluirVisível)
                                    <li x-on:click.stop>
                                        {{ $açãoExcluirInstância }}
                                    </li>
                                @endif
                            </ul>
                        @endif
                    </li>
                @endforeach
            </ul>
        @endif

        @if ($éAdicionável && $açãoAdicionar->isVisible())
            <div @class([
                'fi-fo-simple-repeater-add flex',
                $alinhamentoAçãoAdicionar instanceof Alignment
                    ? match ($alinhamentoAçãoAdicionar) {
                        Alignment::Start, Alignment::Left => 'justify-start',
                        Alignment::Center => 'justify-center',
                        Alignment::End, Alignment::Right => 'justify-end',
                        default => 'fi-align-' . $alinhamentoAçãoAdicionar->value,
                    }
                    : match ($alinhamentoAçãoAdicionar) {
                        'start', 'left' => 'justify-start',
                        'center', null => 'justify-center',
                        'end', 'right' => 'justify-end',
                        default => $alinhamentoAçãoAdicionar,
                    },
            ])>
                {{ $açãoAdicionar }}
            </div>
        @endif
    </div>
</x-dynamic-component>
