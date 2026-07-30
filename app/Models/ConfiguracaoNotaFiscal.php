<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Crypt;

/**
 * @property int $id
 * @property string $cnpj
 * @property string|null $inscricao_municipal
 * @property string $razao_social
 * @property string|null $nome_fantasia
 * @property string|null $cep
 * @property string|null $logradouro
 * @property string|null $numero
 * @property string|null $complemento
 * @property string|null $bairro
 * @property string $codigo_municipio_ibge
 * @property string $uf
 * @property int $regime_especial_tributacao
 * @property bool $optante_simples_nacional
 * @property bool $incentivador_cultural
 * @property string $item_lista_servico
 * @property string|null $codigo_tributacao_municipio
 * @property float $aliquota_iss
 * @property string|null $discriminacao_servico
 * @property string|null $caminho_certificado
 * @property string|null $senha_certificado
 * @property string $serie_rps
 * @property int $ultimo_numero_rps
 * @property string $ambiente
 * @property string|null $url_webservice_homologacao
 * @property string|null $url_webservice_producao
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class ConfiguracaoNotaFiscal extends BaseModel
{
    use HasFactory;

    protected $table = 'configuracoes_nota_fiscal';

    protected $guarded = [];

    protected $casts = [
        'optante_simples_nacional' => 'boolean',
        'incentivador_cultural' => 'boolean',
        'aliquota_iss' => 'decimal:2',
        'regime_especial_tributacao' => 'integer',
        'ultimo_numero_rps' => 'integer',
        'atividades' => 'array',
        'cnaes' => 'array',
    ];

    public function getAtividadePrincipalAttribute(): ?array
    {
        $atividades = $this->atividades ?? [];

        if (empty($atividades)) {
            return null;
        }

        $primeiroCodigo = array_key_first($atividades);
        $item = $atividades[$primeiroCodigo];

        if (is_array($item)) {
            foreach ($atividades as $act) {
                if (! empty($act['is_principal'])) {
                    return $act;
                }
            }

            return $atividades[0] ?? null;
        }

        return [
            'item_lista_servico' => (string) $primeiroCodigo,
            'codigo_tributacao_municipio' => (string) $primeiroCodigo,
            'descricao' => (string) $item,
        ];
    }

    public function getCnaePrincipalAttribute(): ?array
    {
        $cnaes = $this->cnaes ?? [];

        if (empty($cnaes)) {
            return null;
        }

        $primeiroCodigo = array_key_first($cnaes);
        $item = $cnaes[$primeiroCodigo];

        if (is_array($item)) {
            foreach ($cnaes as $cnae) {
                if (! empty($cnae['is_principal'])) {
                    return $cnae;
                }
            }

            return $cnaes[0] ?? null;
        }

        return [
            'codigo' => (string) $primeiroCodigo,
            'descricao' => (string) $item,
        ];
    }

    public function setSenhaCertificadoAttribute(?string $valor): void
    {
        $this->attributes['senha_certificado'] = $valor ? Crypt::encryptString($valor) : null;
    }

    public function getSenhaCertificadoDescriptografadaAttribute(): ?string
    {
        if (empty($this->attributes['senha_certificado'])) {
            return null;
        }

        try {
            return Crypt::decryptString($this->attributes['senha_certificado']);
        } catch (\Throwable $e) {
            return null;
        }
    }

    public static function obterConfiguracaoAtiva(): ?self
    {
        return static::first();
    }
}
