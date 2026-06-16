<?php

namespace App\Models;

use App\Casts\DatetimeWithTimezone;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int|null $paciente_id
 * @property string|null $nome_paciente
 * @property string|null $whatsapp_paciente
 * @property Carbon $data_inicio
 * @property Carbon $data_fim
 * @property string $status
 * @property string|null $observacoes
 * @property string|null $google_evento_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property Paciente|null $paciente
 * @property-read string $obter_nome_paciente
 * @property-read string $obter_whatsapp_paciente
 */
class Agenda extends BaseModel
{
    use SoftDeletes;

    protected $table = 'agendas';

    protected $fillable = [
        'paciente_id',
        'nome_paciente',
        'whatsapp_paciente',
        'data_inicio',
        'data_fim',
        'status',
        'observacoes',
        'google_evento_id',
    ];

    protected $casts = [
        'data_inicio' => DatetimeWithTimezone::class,
        'data_fim' => DatetimeWithTimezone::class,
    ];

    /**
     * Relacionamento com o Paciente (opcional).
     */
    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }

    /**
     * Acessor para obter o nome do paciente, seja ele cadastrado ou não.
     */
    public function getObterNomePacienteAttribute(): string
    {
        if ($this->paciente_id && $this->paciente) {
            return $this->paciente->nome;
        }

        return $this->nome_paciente ?? 'Paciente Não Identificado';
    }

    /**
     * Acessor para obter o celular/whatsapp do paciente, seja ele cadastrado ou não.
     */
    public function getObterWhatsappPacienteAttribute(): string
    {
        if ($this->paciente_id && $this->paciente) {
            return $this->paciente->celular ?? '';
        }

        return $this->whatsapp_paciente ?? '';
    }

    /**
     * Verifica se existe alguma consulta ativa que conflite com o período informado.
     */
    public static function possuiConflito(string|\Carbon\Carbon $dataInicio, string|\Carbon\Carbon $dataFim, ?int $excluirId = null): bool
    {
        $inicio = \Carbon\Carbon::parse($dataInicio);
        $fim = \Carbon\Carbon::parse($dataFim);

        $query = self::query()
            ->where('status', '!=', 'cancelada')
            ->where(function ($q) use ($inicio, $fim) {
                $q->where(function ($sub) use ($inicio, $fim) {
                    $sub->where('data_inicio', '<', $fim)
                        ->where('data_fim', '>', $inicio);
                });
            });

        if ($excluirId) {
            $query->where('id', '!=', $excluirId);
        }

        return $query->exists();
    }
}
