<x-filament-panels::page>
    <div>
        <div class="flex items-center justify-between gap-2 flex-wrap">
            <div class="flex gap-1 items-center">
                {{ $this->createExameAction }}
            </div>

            <div class="inline-flex items-center p-1 bg-gray-100 dark:bg-gray-800 rounded-lg text-xs font-medium border border-gray-200 dark:border-gray-700 shadow-xs">
                <span class="text-xs text-gray-500 dark:text-gray-400 px-2 select-none">Modo:</span>
                <button type="button"
                    wire:click="alternarModoSelecao('checkbox')"
                    class="flex items-center gap-1.5 px-3 py-1.5 rounded-md transition-all duration-150 {{ $modoSelecao === 'checkbox' ? 'bg-white dark:bg-gray-900 text-primary-600 dark:text-primary-400 shadow-sm font-semibold' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200' }}">
                    <x-filament::icon icon="heroicon-o-list-bullet" class="h-4 w-4" />
                    <span>Lista</span>
                </button>
                <button type="button"
                    wire:click="alternarModoSelecao('select')"
                    class="flex items-center gap-1.5 px-3 py-1.5 rounded-md transition-all duration-150 {{ $modoSelecao === 'select' ? 'bg-white dark:bg-gray-900 text-primary-600 dark:text-primary-400 shadow-sm font-semibold' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200' }}">
                    <x-filament::icon icon="heroicon-o-magnifying-glass" class="h-4 w-4" />
                    <span>Busca Rápida</span>
                </button>
            </div>
        </div>

        @if ($datas)
            <table class="table-bordered text-sm mt-4 ">
                <thead>
                    <tr>
                        <th>Nº</th>
                        <th>Testadores</th>
                        @foreach ($datas as $data)
                            <th>
                                <div class="flex justify-between">
                                    <x-filament::icon-button icon="heroicon-o-pencil-square" size="xs"
                                        tooltip="Editar"
                                        wire:click="mountAction('editExame', { id: {{ $data['id'] }} })" />
                                    <x-filament::icon-button icon="heroicon-o-printer" :href="route('biorressonancia.print', $data['id'])"
                                        tooltip="Imprimir" size="xs" tag="a" target="_blank"
                                        label="Filament" />
                                </div>
                                <div>{{ $data['data'] }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 font-normal">{{ $data['ano'] }}</div>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($tableData as $row)
                        @if ($loop->first || $row['categoria'] != $previousCategory)
                            <tr>
                                <td class="table-category"></td>
                                <td class="table-category">
                                    <strong>{{ $row['categoria'] }}</strong>
                                </td>
                                @foreach ($datas as $data)
                                    <td class="table-category"></td>
                                @endforeach
                            </tr>
                            @php $previousCategory = $row['categoria']; @endphp
                        @endif
                        <tr>
                            <td>{{ $row['numero'] }}</td>
                            <td style="text-align: left">{{ $row['nome'] }}</td>
                            @foreach ($datas as $data)
                                <td>{{ $row['id_' . $data['id']] }}</td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
    <x-filament-actions::modals />

    @script
    <script>
        window.configurarLimpezaBuscaSelect = function(componente) {
            var tentarConfigurar = function(restantes) {
                if (!componente || !componente.select) {
                    if (restantes > 0) {
                        setTimeout(function() { tentarConfigurar(restantes - 1); }, 50);
                    }
                    return;
                }

                if (componente.select._limpezaConfigurada) return;
                componente.select._limpezaConfigurada = true;

                var selecaoOriginal = componente.select.selectOption.bind(componente.select);
                componente.select.selectOption = function(valor) {
                    selecaoOriginal(valor);

                    if (this.searchInput) {
                        this.searchInput.value = '';
                        this.searchQuery = '';

                        if (!this.hasDynamicOptions && this.originalOptions) {
                            this.options = JSON.parse(JSON.stringify(this.originalOptions));
                        }

                        this.renderOptions();
                    }
                };
            };

            tentarConfigurar(10);
        };
    </script>
    @endscript
</x-filament-panels::page>
