<?php

namespace App\Http\Controllers;

use App\Models\Exame;
use App\Models\Setting;
use App\Services\PDFGeneratorService;
use Illuminate\Http\Exceptions\HttpResponseException;

class BiorressonanciaController extends Controller
{
    public function print($id)
    {
        // Busca todas as configurações
        $settings = Setting::getAllSettings();

        // Busca o exame
        $exame = Exame::with([
            'paciente',
            'testadores.categoria'
        ])->where('id', $id)->first();

        if ($exame) {
            // Organizar os testadores por categoria
            $categorias = [];

            foreach ($exame->testadores as $testador) {
                $categoria = $testador->categoria;
                $key = $categoria->ordem . ' - ' . $categoria->nome;
                $categoriaNome = $categoria->nome;
                $categoriaNota = $categoria->nota;

                // Agrupar testadores por categoria
                if (!isset($categorias[$key])) {
                    $categorias[$key] = [
                        'nome' => $categoriaNome,
                        'nota' => $categoriaNota,
                        'testadores' => [],
                    ];
                }

                $categorias[$key]['testadores'][] = $testador;
            }

            // Ordenar as categorias
            ksort($categorias);

            $pdfGeneratorService = new PDFGeneratorService(
                'A4',
                'P',
                $exame->paciente,
                $exame->data
            );

            $mpdf = $pdfGeneratorService->generatePDF(
                view('pdf.biorressonancia', ['exame' => $exame, 'categorias' => $categorias,  'settings' => $settings])->render(),
            );

            return $mpdf->Output(str_replace(' ', '_', $exame->paciente->nome) . ' _ ' . time() . '.pdf', 'I');
        } else {
            throw new HttpResponseException(response()->make(
                'Exame não encontrado.',
                404
            ));
        }
    }
}
