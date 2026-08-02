<?php

namespace App\Http\Controllers;

use App\Models\NotaFiscal;
use App\Services\NotaFiscal\DadosImpressaoNotaFiscalService;
use Illuminate\Http\Request;

class NotaFiscalImpressaoController extends Controller
{
    public function imprimir($id, Request $request, DadosImpressaoNotaFiscalService $servicoImpressao)
    {
        $notaFiscal = NotaFiscal::with(['paciente.cidade', 'tratamento', 'usuario'])->findOrFail($id);

        $dados = $servicoImpressao->obterDadosImpressao($notaFiscal);

        return view('notas-fiscais.impressao', [
            'notaFiscal' => $notaFiscal,
            'dados' => $dados,
        ]);
    }
}
