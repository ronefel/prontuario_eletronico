<?php

namespace App\Http\Controllers;

use App\Models\Prontuario;
use App\Models\Setting;
use Illuminate\Http\Request;
use Mpdf\Mpdf;

class ProntuarioController extends Controller
{
    public function print($id, Request $request)
    {
        // Busca o prontuário
        $prontuario = Prontuario::findOrFail($id);

        // Recuperar os parâmetros adicionais da URL
        $layout = $request->query('layout', 'P'); // 'portrait' é o valor padrão
        $paperSize = $request->query('paper_size', 'A4'); // 'A4' é o valor padrão

        // Busca todas as configurações
        $settings = Setting::getAllSettings();

        // Busca os valores de cabeçalho e rodapé do modelo Setting
        $cabecalho = $settings[Setting::CABECALHO];
        $rodape = $settings[Setting::RODAPE];

        // Variáveis dinâmicas que serão substituídas
        // ================================== NÃO ALTERAR ESTAS VARIÁVEIS ==================================
        $variaveis = [
            '{PAC_NOME}' => $prontuario->paciente->nome,
            '{NASCIM}' => $prontuario->paciente->nascimento->format('d/m/Y'),
            '{IDADE}' => $prontuario->paciente->idade(),
            '{SEXO}' => $prontuario->paciente->sexo(),
            '{TIPO}' => $prontuario->paciente->tiposanguineo,
            '{PAC_CPF}' => $prontuario->paciente->cpf,
            '{PAC_CELULAR}' => $prontuario->paciente->celularFormatado(),
            '{PAC_EMAIL}' => $prontuario->paciente->email,
            '{PAC_CEP}' => $prontuario->paciente->cep,
            '{PAC_LOGRADOURO}' => $prontuario->paciente->logradouro,
            '{PAC_NUMERO}' => $prontuario->paciente->numero,
            '{PAC_BAIRRO}' => $prontuario->paciente->bairro,
            '{PAC_COMPLEMENTO}' => $prontuario->paciente->complemento,
            '{PAC_CIDADE}' => $prontuario->paciente->cidade?->cidadeUf(),

            '{DATA_ATENDIMENTO}' => $prontuario->data->format('d/m/Y'),
        ];


        // Substitui os placeholders no cabecalho, descricao e rodape
        $cabecalho = str_replace(array_keys($variaveis), array_values($variaveis), $cabecalho);
        $prontuario->descricao = str_replace(array_keys($variaveis), array_values($variaveis), $prontuario->descricao);
        $rodape = str_replace(array_keys($variaveis), array_values($variaveis), $rodape);

        // Substitui <hr /> por <div style="border-bottom: 2px solid #000;"></div>
        $prontuario->descricao = str_replace('<hr />', '<div style="border-bottom: 1px solid #000;"></div>', $prontuario->descricao);
        $cabecalho = str_replace('<hr />', '<div style="border-bottom: 2px solid #000;"></div>', $cabecalho);
        $rodape = str_replace('<hr />', '<div style="border-bottom: 2px solid #000;"></div>', $rodape);

        $margemSuperior = $settings[Setting::MARGEM_SUPERIOR];
        $margemInferior = $settings[Setting::MARGEM_INFERIOR];
        $margemEsquerda = $settings[Setting::MARGEM_ESQUERDA];
        $margemDireita = $settings[Setting::MARGEM_DIREITA];
        $alturaCabecalho = $settings[Setting::ALTURA_CABECALHO];
        $alturaRodape = $settings[Setting::ALTURA_RODAPE];

        $formato = $paperSize . '-' . $layout;
        if ($paperSize === 'A5noA4') {
            $formato = 'A4-L';
            $margemDireita += 148;
        }


        // Configurando o mPDF
        $mpdf = new Mpdf([
            'format' => $formato,
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
            'setAutoTopMargin' => 'stretch',
            'setAutoBottomMargin' => 'stretch',
            'autoMarginPadding' => -7,
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
        return $mpdf->Output(str_replace(' ', '_', $prontuario->paciente->nome) . ' _ ' . time() . '.pdf', 'I');
    }
}
