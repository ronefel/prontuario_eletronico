<x-filament-panels::page>

    <div x-data="{ drawerOpen: false }" class="md:hidden">
        <!-- Botão para abrir o drawer -->
        <span @click="drawerOpen = true" class="dark:text-gray-300 font-bold text-1xl">
            <x-filament::link size="1xl" icon="heroicon-m-information-circle" class="cursor-pointer">
                {{ $this->paciente->nome }}
            </x-filament::link>
        </span>

        <!-- Overlay do Drawer -->
        <div x-show="drawerOpen" @click="drawerOpen = false" class="fixed inset-0 bg-gray-950/50 dark:bg-gray-950/75 z-40"
            x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"></div>

        <!-- Drawer -->
        <div x-show="drawerOpen" class="fixed inset-y-0 right-0 w-80 bg-white dark:bg-gray-900 z-50 transform shadow-xl"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="translate-x-full"
            x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full">
            <div class="flex flex-col mt-8 p-4  bg-white dark:divide-white/10 dark:bg-gray-900 dark:ring-white/10"
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
                <div class="flex py-2">
                    <span class="dark:text-gray-300">Celular: <x-filament::link size="xl"
                            href="https://wa.me/+55{{ $this->paciente->celular }}" target="_blank">
                            {{ $this->paciente->celular }}</x-filament::link></span>
                </div>
                <div class="flex py-2 whitespace-pre-wrap">
                    <span class="dark:text-gray-300">{{ $this->paciente->observacao }}</span>

                </div>
            </div>
        </div>
    </div>




    <div class="grid sm:grid-cols-1 md:grid-cols-3 gap-4">
        <div class="col-span-2">

            {{ $this->createAction }}

            {{-- <x-filament::button style="{{ $this->formClosed ? '' : 'display: none;' }}" wire:click="showForm()">
                Novo Evento
            </x-filament::button>


            <x-filament-panels::form style="{{ $this->formClosed ? 'display: none;' : '' }}" wire:submit="create">
                {{ $this->form }}

                <div class="fi-ac gap-3 flex flex-wrap items-center justify-start">
                    <x-filament::button type="submit" form="submit" wire:loading.attr="disabled" wire:target="create">
                        Salvar
                    </x-filament::button>

                    <x-filament::button color="danger" type="button" wire:click="cancel()">
                        Cancelar
                    </x-filament::button>
                </div>
            </x-filament-panels::form> --}}

            <p><br></p>

            @php
                // Agrupa os prontuarios por data (sem horas)
                $groupedProntuarios = $this->paciente->prontuarios->groupBy(function ($prontuario) {
                    return \Carbon\Carbon::parse($prontuario->data)->format('Y-m-d');
                });

                // Ordena as datas em ordem decrescente
                $sortedDates = $groupedProntuarios->keys()->sortByDesc(function ($date) {
                    return \Carbon\Carbon::parse($date);
                });

                // Ordena os prontuarios por hora dentro de cada data
                $sortedProntuarios = $sortedDates->flatMap(function ($date) use ($groupedProntuarios) {
                    return $groupedProntuarios[$date]->sortBy(function ($prontuario) {
                        return \Carbon\Carbon::parse($prontuario->data)->format('H:i');
                    });
                });
            @endphp

            <ol class="relative border-s border-gray-200 dark:border-gray-700">
                @php
                    $previousDate = null;
                @endphp
                @foreach ($sortedProntuarios as $prontuario)
                    @php
                        // Converte $prontuario->data para uma instância de Carbon e formata para comparar somente a data
                        $currentDate = \Carbon\Carbon::parse($prontuario->data)->format('Y-m-d');
                    @endphp
                    <li class="ms-6 pt-1 {{ $previousDate != $currentDate ? 'mt-4' : '' }}">

                        @if ($previousDate != $currentDate)
                            <span
                                class="absolute flex items-center justify-center w-6 h-6 bg-blue-100 rounded-full -start-3 ring-white dark:ring-gray-900 dark:bg-blue-900">
                                <svg class="w-2.5 h-2.5 text-blue-800 dark:text-blue-300" aria-hidden="true"
                                    xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                                    <path
                                        d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z" />
                                </svg>
                            </span>
                            <div class="flex">
                                <time
                                    class="block mb-2 mt-1 text-sm font-bold leading-none text-info-400 dark:text-info-400">
                                    {{ \Carbon\Carbon::parse($prontuario->data)->translatedFormat('d \d\e F \d\e Y') }}
                                </time>

                            </div>
                        @endif
                        <div
                            class="flex flex-col items-start justify-between p-4 pt-2 rounded-lg bg-white shadow-sm ring-1 ring-gray-950/5 dark:divide-white/10 dark:bg-gray-900 dark:ring-white/10">
                            <div class="flex w-full justify-between">
                                <span
                                    class="prontuario-hora">{{ \Carbon\Carbon::parse($prontuario->data)->translatedFormat('H:i') }}</span>
                                <div class="flex w-full justify-end gap-6">
                                    {{ ($this->editAction)(['prontuario' => $prontuario->id]) }}
                                    {{ ($this->printAction)(['prontuario' => $prontuario->id]) }}
                                </div>
                            </div>
                            <div class="document-content document-content-view">
                                {!! $prontuario->descricao !!}
                            </div>
                            @if ($prontuario->getArquivosComUrl())
                                <div class="mt-2 flex flex-col p-2 border rounded-lg">
                                    @foreach ($prontuario->getArquivosComUrl() as $arquivo)
                                        <a href="{{ $arquivo['url'] }}" target="_blank"
                                            class="text-xs text-blue-500 hover:underline">{{ $arquivo['nome'] }}</a>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </li>
                    @php
                        $previousDate = $currentDate;
                    @endphp
                @endforeach
            </ol>



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

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            window.addEventListener('openUrlInNewTab', function(event) {
                window.open(event.detail[0].url, '_blank');
            });
        });
    </script>
@endpush
