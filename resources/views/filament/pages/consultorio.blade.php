<x-filament::page>
    <div
        x-data="{ tab: @entangle('activeTab') }"
    >
        <!-- Abas -->
        <div class="flex items-center space-x-1 border-b border-gray-200 dark:border-gray-700 bg-gray-50/40 dark:bg-gray-800/40 rounded-t-lg p-1">
            <template x-for="(label, name) in {
                prontuario: 'Prontuário',
                bioressonancia: 'Biorressonancia',
                tratamentos: 'Tratamentos'
            }" :key="name">
                <button
                    x-on:click="tab = name"
                    class="relative px-4 py-2 text-sm font-medium rounded-md transition-all duration-200"
                    :class="tab === name
                        ? 'text-primary-700 dark:text-primary-400 bg-white dark:bg-gray-900 shadow-sm'
                        : 'text-gray-600 dark:text-gray-300 hover:text-primary-600 hover:bg-white/60 dark:hover:bg-gray-900/60'"
                >
                    <span x-text="label"></span>
                    <!-- Linha animada da aba ativa -->
                    <span
                        class="absolute bottom-0 left-0 w-full h-0.5 bg-primary-600 rounded-full transition-all duration-300"
                        :class="tab === name ? 'opacity-100 scale-x-100' : 'opacity-0 scale-x-50'"
                    ></span>
                </button>
            </template>
        </div>

        <!-- Conteúdo das Abas -->
        <div >
            <div x-show="tab === 'prontuario'" x-cloak>
                @livewire(
                    App\Filament\Resources\PacienteResource\Pages\ProntuarioPaciente::class,
                    ['record' => $paciente->id],
                    key('prontuario-' . $paciente->id)
                )
            </div>

            <div x-show="tab === 'biorressonancia'" x-cloak>
                @livewire(
                    App\Filament\Resources\PacienteResource\Pages\Biorressonancia::class,
                    ['record' => $paciente->id],
                    key('biorressonancia-' . $paciente->id)
                )
            </div>

            <div x-show="tab === 'tratamentos'" x-cloak>
                <p class="text-sm text-gray-700 dark:text-gray-300 pt-4">Conteúdo da aba <strong>Tratamentos</strong>...</p>
            </div>
        </div>
    </div>
</x-filament::page>
