<div>
    <h3 class="text-lg font-bold mb-2">Aplicações do Tratamento</h3>
    @if ($record->aplicacoes->isEmpty())
        <p class="text-gray-500">Nenhuma aplicação registrada.</p>
    @else
        <div class="relative overflow-x-auto">
            <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-6 py-3">Data</th>
                        <th scope="col" class="px-6 py-3">Status</th>
                        <th scope="col" class="px-6 py-3">Itens</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($record->aplicacoes as $aplicacao)
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                            <td class="px-6 py-4">{{ $aplicacao->data_aplicacao?->format('d/m/Y H:i') }}</td>
                            <td class="px-6 py-4">
                                <span
                                    class="px-2 py-1 font-semibold rounded-full
                                    @if ($aplicacao->status === 'aplicada') bg-green-100 text-green-800
                                    @elseif($aplicacao->status === 'revertida') bg-red-100 text-red-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                    {{ ucfirst($aplicacao->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <ul class="list-disc pl-4">
                                    @foreach ($aplicacao->lotes as $lote)
                                        <li>{{ $lote->produto->nome ?? 'Produto' }} - {{ $lote->pivot->quantidade }} un.
                                        </li>
                                    @endforeach
                                </ul>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
