@if ($paciente)
    <div class="flex flex-col gap-4 pb-4 sm:flex-row sm:items-center ">
        <div class="flex items-center gap-4 text-sm">

            <div class="flex flex-col">
                <div class="flex items-center gap-2">
                    <span class="text-base font-semibold text-gray-950 dark:text-white">
                        <x-filament::link :href="route('filament.admin.resources.pacientes.edit', $paciente->id)" title="Editar paciente" color="primary">
                            {{ $paciente->nome }}
                        </x-filament::link>
                    </span>

                    @if ($paciente->tiposanguineo)
                        <x-filament::badge color="danger" size="sm">
                            <div class="flex items-center">
                                <x-filament::icon icon="heroicon-m-heart" class="w-3 h-3 me-1" />
                                {{ $paciente->tiposanguineo }}
                            </div>
                        </x-filament::badge>
                    @endif
                </div>

                <div class="flex flex-wrap items-center mt-1 text-gray-500 gap-x-3 gap-y-1 dark:text-gray-400">
                    <div class="flex items-center gap-1.5" title="Idade">
                        <x-filament::icon icon="heroicon-m-calendar-days"
                            class="w-4 h-4 text-gray-400 dark:text-gray-500" />
                        <span>Idade: {{ $paciente->idade() }}</span>
                    </div>

                    <span class="text-gray-300 dark:text-gray-600">&bull;</span>

                    <div class="flex items-center gap-1.5" title="Sexo">
                        <x-filament::icon icon="heroicon-m-user" class="w-4 h-4 text-gray-400 dark:text-gray-500" />
                        <span>{{ $paciente->sexo() }}</span>
                    </div>

                    @if ($paciente->celular)
                        <span class="text-gray-300 dark:text-gray-600">&bull;</span>

                        <div class="flex items-center gap-1.5" title="WhatsApp">
                            <x-filament::icon icon="heroicon-m-phone"
                                class="w-4 h-4 text-gray-400 dark:text-gray-500" />
                            <x-filament::link size="sm"
                                href="https://wa.me/55{{ preg_replace('/\D/', '', $paciente->celular) }}"
                                target="_blank" color="success">
                                {{ $paciente->celular }}
                            </x-filament::link>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        @if ($paciente->observacao)
            <div class="flex items-center shrink-0">
                <x-filament::modal>
                    <x-slot name="trigger">
                        <x-filament::button size="xs" color="warning" icon="heroicon-m-exclamation-triangle"
                            outlined title="Observações do paciente">
                            Observações
                        </x-filament::button>
                    </x-slot>
                    <x-slot name="heading">

                    </x-slot>

                    <div class="text-sm text-gray-950 dark:text-white">
                        {{ $paciente->observacao }}
                    </div>
                </x-filament::modal>
            </div>
        @endif
    </div>
@endif
