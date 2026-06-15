<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int|null $paciente_id
 * @property string|null $nome_paciente
 * @property string|null $whatsapp_paciente
 * @property \Illuminate\Support\Carbon $data_inicio
 * @property \Illuminate\Support\Carbon $data_fim
 * @property string $status
 * @property string|null $observacoes
 * @property string|null $google_evento_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \App\Models\Paciente|null $paciente
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
        'data_inicio' => 'datetime',
        'data_fim' => 'datetime',
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
}
