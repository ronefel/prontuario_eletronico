<?php

namespace App\Models;

use App\Casts\DatetimeWithTimezone;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $paciente_id
 * @property int|null $tratamento_id
 * @property int|null $user_id
 * @property int $numero_rps
 * @property string $serie_rps
 * @property int $tipo_rps
 * @property Carbon $data_emissao_rps
 * @property string|null $numero_nfse
 * @property string|null $codigo_verificacao
 * @property Carbon|null $data_emissao_nfse
 * @property float $valor_servicos
 * @property float $valor_deducoes
 * @property float $valor_pis
 * @property float $valor_cofins
 * @property float $valor_inss
 * @property float $valor_ir
 * @property float $valor_csll
 * @property float $valor_iss
 * @property float $aliquota_iss
 * @property float $outras_retencoes
 * @property float $desconto_incondicionado
 * @property float $desconto_condicionado
 * @property string $item_lista_servico
 * @property string|null $codigo_cnae
 * @property string|null $codigo_tributacao_municipio
 * @property string $discriminacao_servico
 * @property string $codigo_municipio_ibge
 * @property string $status
 * @property string|null $protocolo_lote
 * @property string|null $xml_rps
 * @property string|null $xml_envio
 * @property string|null $xml_retorno
 * @property string|null $codigo_erro
 * @property string|null $mensagem_erro
 * @property string|null $codigo_cancelamento
 * @property string|null $motivo_cancelamento
 * @property Carbon|null $data_cancelamento
 * @property int|null $nota_fiscal_substituida_id
 * @property int|null $nota_fiscal_substituta_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Paciente $paciente
 * @property Tratamento|null $tratamento
 * @property User|null $usuario
 * @property NotaFiscal|null $notaSubstituida
 * @property NotaFiscal|null $notaSubstituta
 */
class NotaFiscal extends BaseModel
{
    use HasFactory;

    protected $table = 'notas_fiscais';

    protected static function booted(): void
    {
        static::saving(function (NotaFiscal $notaFiscal) {
            $configuracao = ConfiguracaoNotaFiscal::obterConfiguracaoAtiva();

            if ($configuracao?->codigo_municipio_ibge) {
                $notaFiscal->codigo_municipio_ibge = $configuracao->codigo_municipio_ibge;
            } elseif (empty($notaFiscal->codigo_municipio_ibge)) {
                $notaFiscal->codigo_municipio_ibge = '1100049';
            }
        });
    }

    protected $casts = [
        'data_emissao_rps' => DatetimeWithTimezone::class,
        'data_emissao_nfse' => DatetimeWithTimezone::class,
        'data_cancelamento' => DatetimeWithTimezone::class,
        'valor_servicos' => 'decimal:2',
        'valor_deducoes' => 'decimal:2',
        'valor_pis' => 'decimal:2',
        'valor_cofins' => 'decimal:2',
        'valor_inss' => 'decimal:2',
        'valor_ir' => 'decimal:2',
        'valor_csll' => 'decimal:2',
        'valor_iss' => 'decimal:2',
        'aliquota_iss' => 'decimal:2',
        'outras_retencoes' => 'decimal:2',
        'desconto_incondicionado' => 'decimal:2',
        'desconto_condicionado' => 'decimal:2',
    ];

    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }

    public function tratamento()
    {
        return $this->belongsTo(Tratamento::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function notaSubstituida()
    {
        return $this->belongsTo(NotaFiscal::class, 'nota_fiscal_substituida_id');
    }

    public function notaSubstituta()
    {
        return $this->belongsTo(NotaFiscal::class, 'nota_fiscal_substituta_id');
    }

    public function ehAutorizada(): bool
    {
        return $this->status === 'autorizada';
    }

    public function ehCancelada(): bool
    {
        return $this->status === 'cancelada';
    }

    public function ehSubstituida(): bool
    {
        return $this->status === 'cancelada' && ! empty($this->nota_fiscal_substituta_id);
    }

    /**
     * Extrai e formata o conteúdo XML limpo de uma resposta que possa estar envelopada em SOAP/HTML entities.
     * Se houver um nó <CompNfse> (da NFS-e), extrai isoladamente o XML da nota específica.
     */
    public static function extrairXmlLimpo(?string $xml, ?string $numeroNfse = null): ?string
    {
        if (empty($xml)) {
            return null;
        }

        libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
        if (! @$dom->loadXML($xml)) {
            return $xml;
        }

        $tagsEnvelopeRetorno = [
            'outputXML',
            'outputXml',
            'GerarNfseResult',
            'SubstituirNfseResult',
            'CancelarNfseResult',
            'ConsultarNfseResposta',
            'return',
            'output',
        ];

        $domAlvo = $dom;

        foreach ($tagsEnvelopeRetorno as $tag) {
            $nos = $dom->getElementsByTagName($tag);
            if ($nos->length > 0) {
                $conteudoInterno = $nos->item(0)->nodeValue;
                if (! empty($conteudoInterno) && (str_contains($conteudoInterno, '<') || str_contains($conteudoInterno, '&lt;'))) {
                    $desempacotado = htmlspecialchars_decode(html_entity_decode($conteudoInterno, ENT_QUOTES | ENT_XML1, 'UTF-8'));
                    $domInterno = new \DOMDocument();
                    if (@$domInterno->loadXML($desempacotado)) {
                        $domAlvo = $domInterno;
                        break;
                    }
                }
            }
        }

        // Tentar isolar o elemento <CompNfse> da nota fiscal específica
        $nosCompNfse = $domAlvo->getElementsByTagName('CompNfse');
        if ($nosCompNfse->length > 0) {
            $noCompEscolhido = null;

            if ($nosCompNfse->length === 1) {
                $noCompEscolhido = $nosCompNfse->item(0);
            } else {
                foreach ($nosCompNfse as $noComp) {
                    if ($noComp instanceof \DOMElement) {
                        $nosNumero = $noComp->getElementsByTagName('Numero');
                        if ($nosNumero->length > 0) {
                            $num = $nosNumero->item(0)->nodeValue;
                            if (! empty($numeroNfse) && $num === (string) $numeroNfse) {
                                $noCompEscolhido = $noComp;
                                break;
                            }
                        }
                    }
                }

                if (! $noCompEscolhido) {
                    $noCompEscolhido = $nosCompNfse->item($nosCompNfse->length - 1);
                }
            }

            if ($noCompEscolhido) {
                $domComp = new \DOMDocument('1.0', 'utf-8');
                $domComp->preserveWhiteSpace = false;
                $domComp->formatOutput = true;
                $noImportado = $domComp->importNode($noCompEscolhido, true);
                $domComp->appendChild($noImportado);

                return $domComp->saveXML();
            }
        }

        $domFinal = new \DOMDocument('1.0', 'utf-8');
        $domFinal->preserveWhiteSpace = false;
        $domFinal->formatOutput = true;
        if (@$domFinal->loadXML($domAlvo->saveXML())) {
            return $domFinal->saveXML();
        }

        return $domAlvo->saveXML();
    }

    /**
     * Retorna o XML limpo pronto para download ou exibição.
     */
    public function obterXmlDownload(): ?string
    {
        $conteudoXml = $this->xml_retorno ?: $this->xml_envio ?: $this->xml_rps;

        return static::extrairXmlLimpo($conteudoXml, $this->numero_nfse);
    }
}
