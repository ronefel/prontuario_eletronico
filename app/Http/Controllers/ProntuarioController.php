<?php

namespace App\Http\Controllers;

use App\Models\Prontuario;
use App\Models\Setting;
use App\Services\PDFGeneratorService;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Mpdf\Mpdf;

class ProntuarioController extends Controller
{
    public function print($id, Request $request)
    {
        // Busca o prontuário
        $prontuario = Prontuario::findOrFail($id);

        // Recuperar os parâmetros adicionais da URL
        $layout    = $request->query('layout', 'P'); // 'portrait' é o valor padrão
        $paperSize = $request->query('paper_size', 'A4'); // 'A4' é o valor padrão

        // Validação manual dos parâmetros
        if (!in_array($layout, ['P', 'L'])) {
            throw new HttpResponseException(response()->make(
                'O parâmetro layout deve ser P (portrait) ou L (landscape).',
                400
            ));
        }

        if (!in_array($paperSize, ['A4', 'A5', 'A5noA4'])) {
            throw new HttpResponseException(response()->make(
                'O parâmetro paper_size deve ser A4, A5 ou A5noA4.',
                400
            ));
        }

        $pdfGeneratorService = new PDFGeneratorService(
            $paperSize,
            $layout,
            $prontuario->paciente,
            $prontuario->data
        );

        $mpdf = $pdfGeneratorService->generatePDF(
            view('pdf.prontuario', ['prontuario' => $prontuario])->render(),
        );

        return $mpdf->Output(str_replace(' ', '_', $prontuario->paciente->nome) . ' _ ' . time() . '.pdf', 'I');
    }
}
