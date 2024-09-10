<?php

namespace App\Http\Controllers;

use App\Models\Prontuario;
use App\Models\Setting;
use Mpdf\Mpdf;

class ProntuarioController extends Controller
{
    public function print($id)
    {
        // Busca o prontuário
        $prontuario = Prontuario::findOrFail($id);

        // Busca todas as configurações
        $settings = Setting::getAllSettings();

        // Busca os valores de cabeçalho e rodapé do modelo Setting
        $cabecalho = $settings[Setting::CABECALHO];
        $rodape = $settings[Setting::RODAPE];

        // Variáveis dinâmicas que serão substituídas
        $variaveis = [
            '{{PACIENTE_NOME}}' => $prontuario->paciente->nome,
            // '{{ENDERECO}}' => $prontuario->paciente->endereco,
            // Adicione mais variáveis conforme necessário
        ];


        // Substitui os placeholders no cabecalho, descricao e rodape
        $cabecalho = str_replace(array_keys($variaveis), array_values($variaveis), $cabecalho);
        $prontuario->descricao = str_replace(array_keys($variaveis), array_values($variaveis), $prontuario->descricao);
        $rodape = str_replace(array_keys($variaveis), array_values($variaveis), $rodape);

        // Substitui <hr /> por <div style="border-bottom: 2px solid #000;"></div>
        $prontuario->descricao = str_replace('<hr />', '<div style="border-bottom: 2px solid #000;"></div>', $prontuario->descricao);
        $cabecalho = str_replace('<hr />', '<div style="border-bottom: 2px solid #000;"></div>', $cabecalho);
        $rodape = str_replace('<hr />', '<div style="border-bottom: 2px solid #000;"></div>', $rodape);

        $margemSuperior = $settings[Setting::MARGEM_SUPERIOR];
        $margemInferior = $settings[Setting::MARGEM_INFERIOR];
        $margemEsquerda = $settings[Setting::MARGEM_ESQUERDA];
        $margemDireita = $settings[Setting::MARGEM_DIREITA];
        $alturaCabecalho = $settings[Setting::ALTURA_CABECALHO];
        $alturaRodape = $settings[Setting::ALTURA_RODAPE];

        // Configurando o mPDF
        $mpdf = new Mpdf([
            'fontDir' => [public_path('fonts/Inter')],
            'fontdata' => [
                'inter' => [
                    'R' => 'Inter-VariableFont_opsz,wght.ttf',  // Caminho para o arquivo de fonte regular
                    'I' => 'Inter-Italic-VariableFont_opsz,wght.ttf', // Caminho para o arquivo de fonte itálico
                ],
            ],
            'default_font' => 'inter',  // Define a fonte Inter como padrão
            'margin_left' => $margemEsquerda,
            'margin_right' => $margemDireita,
            'margin_top' => $alturaCabecalho + $margemSuperior,
            'margin_bottom' => $alturaRodape + $margemInferior,
            'margin_header' => $margemSuperior,
            'margin_footer' => $margemInferior,
        ]);

        // Define o cabeçalho e o rodapé do PDF
        $mpdf->SetHTMLHeader('
            <div class="document-content">
                ' . $cabecalho . '
            </div>
        ');
        $mpdf->SetHTMLFooter('
            <div class="document-content">
                ' . $rodape . '
            </div>
        ');

        // Conteúdo HTML do PDF
        $htmlContent = view('pdf.prontuario', ['prontuario' => $prontuario])->render(); // Pega o conteúdo da view 'pdf.template'

        // Adiciona o CSS
        $stylesheet = file_get_contents('vendor\ckeditor\document-content.css');
        $mpdf->WriteHTML($stylesheet, \Mpdf\HTMLParserMode::HEADER_CSS);

        // Passa o conteúdo HTML para o mPDF
        $mpdf->WriteHTML($htmlContent,);

        // Gera o PDF e retorna
        return $mpdf->Output('document.pdf', 'I');
    }
}
