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
</x-filament-panels::page>
