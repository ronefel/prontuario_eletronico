<x-filament-panels::page>
    <div class="grid grid-cols-3 gap-4">
        <div class="col-span-2" x-data="{ open: false }">
            <x-filament::button x-show="!open" x-on:click="open = ! open">
                Novo Evento
            </x-filament::button>
            <x-filament-panels::form x-show="open">
                {{ $this->form }}

                <x-filament-panels::form.actions :actions="$this->getActions()" />
            </x-filament-panels::form>
            <p><br></p>

            <ol class="relative border-s border-gray-200 dark:border-gray-700">
                <li class="ms-6 pt-1 mt-4">
                    <span class="absolute flex items-center justify-center w-6 h-6 bg-blue-100 rounded-full -start-3 ring-white dark:ring-gray-900 dark:bg-blue-900">
                        <svg class="w-2.5 h-2.5 text-blue-800 dark:text-blue-300" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z" />
                        </svg>
                    </span>
                    <time class="block mb-2 mt-1 text-sm font-bold leading-none text-info-400 dark:text-info-400">20 de janeiro de 2022</time>
                    <h3 class="mb-1 text-lg font-semibold text-gray-900 dark:text-white">Evolução Clínica:</h3>
                    <p>Relato do Paciente: Elaine conseguiu voltar a fazer crochê, atividade que tinha abandonado devido à dor. Relata melhora geral no bem-estar e na qualidade de vida.</p>
                    <br>
                    <p>Exame Físico: Diminuição do inchaço articular e aumento da amplitude de movimento nas mãos.</p>
                    <br>
                    <p>Conclusão:</p>
                    <p>Diagnóstico Final: Artrite reumatoide com significativa melhora dos sintomas após tratamento com ozonioterapia.</p>
                    <p>Recomendações: Manter sessões de ozonioterapia mensais para manutenção da melhora clínica e continuar com fisioterapia regular.</p>
                    <a href="#" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-lg hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:outline-none focus:ring-gray-100 focus:text-blue-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700 dark:focus:ring-gray-700"><svg class="w-3.5 h-3.5 me-2.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M14.707 7.793a1 1 0 0 0-1.414 0L11 10.086V1.5a1 1 0 0 0-2 0v8.586L6.707 7.793a1 1 0 1 0-1.414 1.414l4 4a1 1 0 0 0 1.416 0l4-4a1 1 0 0 0-.002-1.414Z" />
                            <path d="M18 12h-2.55l-2.975 2.975a3.5 3.5 0 0 1-4.95 0L4.55 12H2a2 2 0 0 0-2 2v4a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-4a2 2 0 0 0-2-2Zm-3 5a1 1 0 1 1 0-2 1 1 0 0 1 0 2Z" />
                        </svg> Anexos</a>
                </li>
                <li class="ms-6 pt-1 mt-4">
                    <span class="absolute flex items-center justify-center w-6 h-6 bg-blue-100 rounded-full -start-3 ring-white dark:ring-gray-900 dark:bg-blue-900">
                        <svg class="w-2.5 h-2.5 text-blue-800 dark:text-blue-300" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z" />
                        </svg>
                    </span>
                    <time class="block mb-2 mt-1 text-sm font-bold leading-none text-info-400 dark:text-info-400">04 de janeiro de 2022</time>
                    <h3 class="mb-1 text-lg font-semibold text-gray-900 dark:text-white">Sexta Sessão:</h3>
                    <p>Procedimento: Aplicação contínua de ozônio nas articulações afetadas.</p>
                    <br>
                    <p>Observações: Paciente relatou melhora notável na funcionalidade das mãos, conseguindo realizar atividades diárias com mais facilidade.</p>
                </li>
                <li class="ms-6 pt-1 mt-4">
                    <span class="absolute flex items-center justify-center w-6 h-6 bg-blue-100 rounded-full -start-3 ring-white dark:ring-gray-900 dark:bg-blue-900">
                        <svg class="w-2.5 h-2.5 text-blue-800 dark:text-blue-300" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z" />
                        </svg>
                    </span>
                    <time class="block mb-2 mt-1 text-sm font-bold leading-none text-info-400 dark:text-info-400">21 de dezembro de 2021</time>
                    <h3 class="mb-1 text-lg font-semibold text-gray-900 dark:text-white">Terceira Sessão:</h3>
                    <p>Procedimento: Reaplicação de ozônio nas mesmas articulações.</p>
                    <br>
                    <p>Observações: Paciente relatou redução significativa na dor e leve melhora na mobilidade.</p>
                </li>
                <li class="ms-6 pt-1 mt-4">
                    <span class="absolute flex items-center justify-center w-6 h-6 bg-blue-100 rounded-full -start-3 ring-white dark:ring-gray-900 dark:bg-blue-900">
                        <svg class="w-2.5 h-2.5 text-blue-800 dark:text-blue-300" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z" />
                        </svg>
                    </span>
                    <time class="block mb-2 mt-1 text-sm font-bold leading-none text-info-400 dark:text-info-400">7 de dezembro de 2021</time>
                    <h3 class="mb-1 text-lg font-semibold text-gray-900 dark:text-white">Primeira Sessão:</h3>
                    <p>Procedimento: Aplicação de ozônio nas articulações metacarpofalângicas e interfalângicas proximais.</p>
                    <br>
                    <p>Observações: Paciente relatou leve melhora na dor.</p>
                </li>
                <li class="ms-6 pt-1 mt-4">
                    <span class="absolute flex items-center justify-center w-6 h-6 bg-blue-100 rounded-full -start-3 ring-white dark:ring-gray-900 dark:bg-blue-900">
                        <svg class="w-2.5 h-2.5 text-blue-800 dark:text-blue-300" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z" />
                        </svg>
                    </span>
                    <time class="block mb-2 mt-1 text-sm font-bold leading-none text-info-400 dark:text-info-400">2 de dezembro de 2021</time>
                    <div class="mce-content-body">
                        <p ><span style="font-size: 14pt;" data-mce-style="font-size: 14pt;"><strong>Histórico Clínico:</strong></span></p>
                        <ul>
                            <li><strong>Condição Principal:</strong> Lesão no joelho direito (lesão do ligamento cruzado anterior - LCA) ocorrida há 3 meses.</li>
                            <li><strong>Histórico Médico:</strong> Avaliação ortopédica inicial recomendou cirurgia, mas paciente optou por explorar terapias alternativas antes de decidir.</li>
                        </ul>
                        <p><br data-mce-bogus="1"></p>
                        <p><strong>Queixa Principal:</strong></p>
                        <ul>
                            <li>Dor intensa no joelho direito, instabilidade e dificuldade para caminhar e realizar atividades esportivas.</li>
                        </ul>
                        <p><br data-mce-bogus="1"></p>
                        <p><strong>Exame Físico:</strong></p>
                        <ul>
                            <li><strong>Joelho:</strong> Inchaço moderado, dor ao toque e limitação de movimento.</li>
                            <li><strong>Teste de Estabilidade:</strong> Instabilidade significativa observada durante o teste de Lachman.</li>
                        </ul>
                        <p><br data-mce-bogus="1"></p>
                        <p><strong>Plano de Tratamento:</strong></p>
                        <ul>
                            <li><strong>Objetivo:</strong> Redução da dor e inflamação, aumento da estabilidade e funcionalidade do joelho.</li>
                            <li><strong>Terapia Proposta:</strong> Ozonioterapia intra-articular e percutânea.</li>
                        </ul>
                    </div>
                </li>
            </ol>


        </div>
        <div class="flex flex-col mt-8 p-4 rounded-lg bg-white shadow-sm ring-1 ring-gray-950/5 dark:divide-white/10 dark:bg-gray-900 dark:ring-white/10" style="align-self: flex-start;">
            <div class="flex">
                <span class="dark:text-gray-300 font-bold text-2xl">
                    <x-filament::link size="2xl" :href="route('filament.admin.resources.pacientes.edit', $this->paciente->id)" tooltip="Editar paciente">
                        {{$this->paciente->nome}}
                    </x-filament::link>
                </span>

            </div>
            <div class="flex py-2">
                <!-- <x-heroicon-s-cake class="w-5 h-5 text-gray-400 dark:text-gray-300 mr-2 ml-4" /> -->
                <span class="dark:text-gray-300">Idade: {{$this->paciente->idade()}}</span>
            </div>
            <div class="flex py-2">
                <span class="dark:text-gray-300">Sexo: {{$this->paciente->sexo()}}</span>
            </div>
            <div class="flex py-2">
                <span class="dark:text-gray-300">Celular: <x-filament::link size="xl" href="https://wa.me/+55{{$this->paciente->celular}}" target="_blank">{{$this->paciente->celular}}</x-filament::link></span>
            </div>
            <div class="flex py-2 whitespace-pre-wrap">
                <span class="dark:text-gray-300">{{$this->paciente->observacao}}</span>

            </div>
        </div>
    </div>

    <x-filament-actions::modals />
</x-filament-panels::page>
