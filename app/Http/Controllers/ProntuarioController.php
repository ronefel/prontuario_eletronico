<?php

namespace App\Http\Controllers;

use App\Models\Prontuario;
use Illuminate\Http\Request;
use Mpdf\Mpdf;

class ProntuarioController extends Controller
{
    public function print($id)
    {
        // $prontuario = Prontuario::findOrFail($id);
        // $pdf = PDF::loadView('pdf.prontuario', ['prontuario' => $prontuario])
        //     ->setPaper('a4') // Define o tamanho do papel, se necessário
        //     ->setOption('enable-local-file-access', true) // Permite o acesso a arquivos locais
        //     ->setOption('defaultFont', 'Inter'); // Define a fonte padrão

        // // Aplicar o CSS
        // $pdf->setOptions([
        //     'css' => public_path('vendor\ckeditor\document-content.css'),
        // ]);

        // return $pdf->stream('prontuario.pdf');

        // Caminho para a pasta de fontes na pasta public/fonts
        $prontuario = Prontuario::findOrFail($id);
        $fontDir = public_path('fonts/Inter');

        // Configurando o mPDF
        $mpdf = new Mpdf([
            'fontDir' => [
                $fontDir
            ],
            'fontdata' => [
                'inter' => [
                    'R' => 'Inter-VariableFont_opsz,wght.ttf',  // Caminho para o arquivo de fonte regular
                    'I' => 'Inter-Italic-VariableFont_opsz,wght.ttf', // Caminho para o arquivo de fonte itálico
                ],
            ],
            'default_font' => 'inter',  // Define a fonte Inter como padrão
            'margin_left' => 6,
            'margin_right' => 5,
            'margin_top' => 5,
            'margin_bottom' => 5,
            'margin_header' => 9,
            'margin_footer' => 9,
        ]);

        // Conteúdo HTML do PDF
        $htmlContent = view('pdf.prontuario', ['prontuario' => $prontuario])->render(); // Pega o conteúdo da view 'pdf.template'

        $stylesheet = file_get_contents('vendor\ckeditor\document-content.css');
        $mpdf->WriteHTML($stylesheet, \Mpdf\HTMLParserMode::HEADER_CSS);
        // Passa o conteúdo HTML para o mPDF
        $mpdf->WriteHTML($htmlContent,);

        // Gera o PDF e força o download
        return $mpdf->Output('document.pdf', 'I');
    }
}
