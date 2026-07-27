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
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Paciente $paciente
 * @property Tratamento|null $tratamento
 * @property User|null $usuario
 */
class NotaFiscal extends BaseModel
{
    use HasFactory;

    protected $table = 'notas_fiscais';

    protected $casts = [
        'data_emissao_rps' => DatetimeWithTimezone::class,
        'data_emissao_nfse' => DatetimeWithTimezone::class,
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

    public function ehAutorizada(): bool
    {
        return $this->status === 'autorizada';
    }

    public function ehCancelada(): bool
    {
        return $this->status === 'cancelada';
    }
}
