<x-filament-panels::page>
    @php
        $configuracao = $this->getRecord();
        $estaConectado = $configuracao->estaConectado();
    @endphp

    {{-- Seção de Integração com o Google Calendar --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-100 dark:border-gray-700 transition-all duration-300">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="p-3 bg-red-50 dark:bg-red-950/30 rounded-lg text-red-600 dark:text-red-400">
                    <x-heroicon-o-link class="h-6 w-6" />
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Integração com Google Calendar</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Gerencie a conexão e sincronização com o Google Calendar.</p>
                </div>
            </div>
            
            <div class="flex flex-wrap items-center gap-3">
                {{-- Indicador de Status --}}
                <div class="flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-semibold {{ $estaConectado ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/20 dark:text-emerald-400' : 'bg-amber-50 text-amber-700 dark:bg-amber-950/20 dark:text-amber-400' }}">
                    <span class="h-2.5 w-2.5 rounded-full animate-pulse {{ $estaConectado ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                    {{ $estaConectado ? 'Conectado' : 'Desconectado' }}
                </div>

                {{-- Ações de Integração --}}
                <div class="flex items-center gap-2">
                    @if ($estaConectado)
                        <button 
                            type="button"
                            wire:click="desconectarGoogle"
                            wire:confirm="Tem certeza de que deseja desconectar o Google Calendar? As consultas criadas não serão mais enviadas para lá."
                            class="fi-btn fi-btn-color-danger relative inline-flex items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-btn-size-md gap-1.5 px-3 py-2 text-sm text-white bg-red-600 hover:bg-red-500 dark:bg-red-500 dark:hover:bg-red-400"
                        >
                            <x-heroicon-m-power class="h-4 w-4" />
                            <span>Desconectar</span>
                        </button>
                    @elseif (!empty($configuracao->client_id))
                        <button 
                            type="button"
                            wire:click="conectarGoogle"
                            class="fi-btn fi-btn-color-primary relative inline-flex items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-btn-size-md gap-1.5 px-3 py-2 text-sm text-white bg-primary-600 hover:bg-primary-500 dark:bg-primary-500 dark:hover:bg-primary-400"
                        >
                            <x-heroicon-m-arrow-path class="h-4 w-4" />
                            <span>Conectar Agenda</span>
                        </button>
                    @else
                        <span class="text-xs text-gray-400 italic">Preencha as credenciais do Google abaixo para conectar.</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Formulário de Configuração Geral --}}
    <form wire:submit="salvar" class="space-y-6">
        {{ $this->form }}
    </form>
</x-filament-panels::page>
