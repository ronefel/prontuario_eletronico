<?php

namespace App\Http\Controllers;

use App\Models\Exame;
use App\Models\Setting;
use App\Services\PDFGeneratorService;
use Illuminate\Http\Request;

class BiorressonanciaController extends Controller
{
    public function print($id)
    {
        // Busca o exame
        $exame = Exame::with(['testadores', 'paciente'])->where('id', $id)->orderBy('data')->first();

        // Busca todas as configurações
        $settings = Setting::getAllSettings();

        $pdfGeneratorService = new PDFGeneratorService(
            'A4',
            'P',
            $exame->paciente,
            $exame->data
        );

        $mpdf = $pdfGeneratorService->generatePDF(
            view('pdf.biorressonancia', ['exame' => $exame, 'settings' => $settings])->render(),
        );

        return $mpdf->Output(str_replace(' ', '_', $exame->paciente->nome) . ' _ ' . time() . '.pdf', 'I');
    }
}
