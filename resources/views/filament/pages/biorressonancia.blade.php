<x-filament-panels::page>
    <div>
        <div class="flex gap-1">
            {{ $this->createExameAction }}

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
                                {{ $data['data'] }}
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
</x-filament-panels::page>
