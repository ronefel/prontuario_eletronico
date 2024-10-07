<?php

namespace App\Services;

use App\Models\Setting;
use Mpdf\Mpdf;

class PDFGeneratorService
{
    protected $formato;
    protected $margemEsquerda;
    protected $margemDireita;
    protected $margemSuperior;
    protected $margemInferior;
    protected $alturaCabecalho;
    protected $alturaRodape;
    // protected $settings;
    protected $paciente;
    protected $dataAtendimento;
    protected $cabecalho;
    protected $rodape;

    public function __construct(
        $paperSize = 'A4',
        $layout = 'P',
        $paciente = null,
        $dataAtendimento = null
    ) {
        $this->paciente = $paciente;
        $this->dataAtendimento = $dataAtendimento;

        // Busca todas as configurações
        $settings = Setting::getAllSettings();

        $this->margemSuperior  = $settings[Setting::MARGEM_SUPERIOR];
        $this->margemInferior  = $settings[Setting::MARGEM_INFERIOR];
        $this->margemEsquerda  = $settings[Setting::MARGEM_ESQUERDA];
        $this->margemDireita   = $settings[Setting::MARGEM_DIREITA];
        $this->alturaCabecalho = $settings[Setting::ALTURA_CABECALHO];
        $this->alturaRodape    = $settings[Setting::ALTURA_RODAPE];

        $this->cabecalho = $this->replaceVariables($settings[Setting::CABECALHO]);
        $this->rodape    = $this->replaceVariables($settings[Setting::RODAPE]);

        $this->cabecalho = $this->replaceHr($this->cabecalho);
        $this->rodape    = $this->replaceHr($this->rodape);

        $this->formato = $paperSize . '-' . $layout;
        if ($paperSize === 'A5noA4') {
            $this->formato = 'A4-L';
            $this->margemDireita += 148;
        }
    }

    protected function replaceVariables($content)
    {
        $variaveis = [
            '{PAC_NOME}'         => $this->paciente->nome,
            '{NASCIM}'           => $this->paciente->nascimento->format('d/m/Y'),
            '{IDADE}'            => $this->paciente->idade(),
            '{SEXO}'             => $this->paciente->sexo(),
            '{TIPO}'             => $this->paciente->tiposanguineo,
            '{PAC_CPF}'          => $this->paciente->cpf,
            '{PAC_CELULAR}'      => $this->paciente->celularFormatado(),
            '{PAC_EMAIL}'        => $this->paciente->email,
            '{PAC_CEP}'          => $this->paciente->cep,
            '{PAC_LOGRADOURO}'   => $this->paciente->logradouro,
            '{PAC_NUMERO}'       => $this->paciente->numero,
            '{PAC_BAIRRO}'       => $this->paciente->bairro,
            '{PAC_COMPLEMENTO}'  => $this->paciente->complemento,
            '{PAC_CIDADE}'       => $this->paciente->cidade?->cidadeUf(),
            '{DATA_ATENDIMENTO}' => $this->dataAtendimento?->format('d/m/Y'),
        ];

        return str_replace(array_keys($variaveis), array_values($variaveis), $content);
    }

    /**
     * Substitui <hr /> por <div style="border-bottom: 1px solid #000;"></div>
     */
    protected function replaceHr($content)
    {
        return str_replace('<hr />', '<div style="border-bottom: 1px solid #000; line-height: 0;"></div>', $content);
    }

    public function generatePDF($htmlContent)
    {
        // Configurando o mPDF com base nas propriedades da classe
        $mpdf = new Mpdf([
            'format' => $this->formato,
            'fontDir' => [public_path('fonts/Roboto')],
            'fontdata' => [
                'roboto' => [
                    'R' => 'Roboto-Regular.ttf',
                    'I' => 'Roboto-Italic.ttf',
                    'B' => 'Roboto-Bold.ttf',
                    'BI' => 'Roboto-BoldItalic.ttf',
                    'L' => 'Roboto-Light.ttf',
                    'LI' => 'Roboto-LightItalic.ttf',
                    'M' => 'Roboto-Medium.ttf',
                    'MI' => 'Roboto-MediumItalic.ttf',
                    'T' => 'Roboto-Thin.ttf',
                    'TI' => 'Roboto-ThinItalic.ttf',
                    'BL' => 'Roboto-Black.ttf',
                    'BI' => 'Roboto-BlackItalic.ttf',
                ],
            ],
            'default_font' => 'roboto',
            'margin_left' => $this->margemEsquerda,
            'margin_right' => $this->margemDireita,
            'margin_top' => $this->alturaCabecalho + $this->margemSuperior,
            'margin_bottom' => $this->alturaRodape + $this->margemInferior,
            'margin_header' => $this->margemSuperior,
            'margin_footer' => $this->margemInferior,
            'setAutoTopMargin' => 'stretch',
            'setAutoBottomMargin' => 'stretch',
            'autoMarginPadding' => -7,
        ]);

        // Define o cabeçalho e o rodapé do PDF
        $mpdf->SetHTMLHeader('
            <div class="document-content">
                ' . $this->cabecalho . '
            </div>
        ');
        $mpdf->SetHTMLFooter('
            <div class="document-content">
                ' . $this->rodape . '
            </div>
        ');

        // Adiciona o conteúdo HTML e o CSS
        $stylesheet = file_get_contents('vendor\ckeditor\document-content.css');
        $mpdf->WriteHTML($stylesheet, \Mpdf\HTMLParserMode::HEADER_CSS);

        $htmlContent = $this->replaceVariables($htmlContent);
        $htmlContent = $this->replaceHr($htmlContent);

        $mpdf->WriteHTML($htmlContent);

        return $mpdf;
    }
}
