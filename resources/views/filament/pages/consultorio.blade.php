<x-filament::page>
    @php
        $tabs = [
            'prontuario' => 'Prontuário',
            'biorressonancia' => 'Biorressonancia',
            'tratamentos' => 'Tratamentos',
        ];
    @endphp

    <div>
        <!-- Abas -->
        <div
            class="flex items-center space-x-1 border-b border-gray-200 dark:border-gray-700 bg-gray-50/40 dark:bg-gray-800/40 rounded-t-lg p-1">
            @foreach ($tabs as $key => $label)
                <button wire:click="setActiveTab('{{ $key }}')"
                    class="relative px-4 py-2 text-sm font-medium rounded-md transition-all duration-200 {{ $activeTab === $key ? 'text-primary-700 dark:text-primary-400 bg-white dark:bg-gray-900 shadow-sm' : 'text-gray-600 dark:text-gray-300 hover:text-primary-600 hover:bg-white/60 dark:hover:bg-gray-900/60' }}">
                    <span>{{ $label }}</span>
                    <!-- Linha animada da aba ativa -->
                    @if ($activeTab === $key)
                        <span
                            class="absolute bottom-0 left-0 w-full h-0.5 bg-primary-600 rounded-full transition-all duration-300"
                            style="opacity: 1; transform: scaleX(1);"></span>
                    @endif
                </button>
            @endforeach
        </div>

        <!-- Conteúdo das Abas -->
        <div class="mt-4">
            <!-- Loading Indicator -->
            <div wire:loading class="w-full p-4 text-center text-gray-500">
                <div class="flex items-center justify-center space-x-2">
                    <x-filament::loading-indicator class="h-5 w-5" />
                    @foreach ($tabs as $key => $label)
                        <span wire:loading.delay.short wire:target="setActiveTab('{{ $key }}')"> Carregando
                            {{ $label }}...</span>
                    @endforeach
                </div>
            </div>

            <div wire:loading.remove>
                @if ($activeTab === 'prontuario')
                    @livewire(App\Filament\Resources\PacienteResource\Pages\ProntuarioPaciente::class, ['record' => $paciente->id], key('prontuario-' . $paciente->id))
                @elseif ($activeTab === 'biorressonancia')
                    @livewire(App\Filament\Resources\PacienteResource\Pages\Biorressonancia::class, ['record' => $paciente->id], key('biorressonancia-' . $paciente->id))
                @elseif ($activeTab === 'tratamentos')
                    @livewire(App\Filament\Resources\TratamentoResource\Pages\ListTratamentos::class, ['pacienteId' => $paciente->id], key('tratamentos-' . $paciente->id))
                @endif
            </div>
        </div>
    </div>
</x-filament::page>
