<x-filament-panels::page>
    <div class="grid sm:grid-cols-1 md:grid-cols-3 gap-4">
        <div class="col-span-2">
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
                                <th wire:click="mountAction('editExame', { id: {{ $data['id'] }} })"
                                    style="cursor: pointer;" class="text-primary-600 dark:text-primary-400">
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
        <div class="invisible md:visible flex flex-col mt-8 p-4 rounded-lg bg-white shadow-sm ring-1 ring-gray-950/5 dark:divide-white/10 dark:bg-gray-900 dark:ring-white/10"
            style="align-self: flex-start;">
            <div class="flex">
                <span class="dark:text-gray-300 font-bold text-2xl">
                    <x-filament::link size="2xl" :href="route('filament.admin.resources.pacientes.edit', $this->paciente->id)" tooltip="Editar paciente">
                        {{ $this->paciente->nome }}
                        <x-heroicon-c-arrow-top-right-on-square class="w-5 h-5 " style="display: initial;" />
                    </x-filament::link>
                </span>
            </div>
            <div class="flex py-2">
                <!-- <x-heroicon-s-cake class="w-5 h-5 text-gray-400 dark:text-gray-300 mr-2 ml-4" /> -->
                <span class="dark:text-gray-300">Idade:
                    {{ $this->paciente->idade() }}</span>
            </div>
            <div class="flex py-2">
                <span class="dark:text-gray-300">Sexo:
                    {{ $this->paciente->sexo() }}</span>
            </div>
            @if ($this->paciente->tiposanguineo)
                <div class="flex py-2">
                    <span class="dark:text-gray-300">Tipo Sanguíneo:
                        {{ $this->paciente->tiposanguineo }}</span>
                </div>
            @endif
            @if ($this->paciente->celular)
                <div class="flex py-2">
                    <span class="dark:text-gray-300">Celular: <x-filament::link size="xl"
                            href="https://wa.me/+55{{ $this->paciente->celular }}" target="_blank">
                            {{ $this->paciente->celular }}</x-filament::link></span>
                </div>
            @endif
            <div class="flex py-2 whitespace-pre-wrap">
                <span class="dark:text-gray-300">{{ $this->paciente->observacao }}</span>

            </div>
        </div>
    </div>
    <x-filament-actions::modals />
</x-filament-panels::page>
