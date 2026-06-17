<?php

namespace App\Models;

/**
 * @property int $id
 * @property string $hora_inicio
 * @property string $hora_fim
 * @property int $intervalo
 * @property string|null $pausa_inicio
 * @property string|null $pausa_fim
 * @property string $modo_limite
 * @property int|null $limite_consultas_dia
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class AgendaConfiguracao extends BaseModel
{
    protected $table = 'agenda_configuracoes';

    protected $fillable = [
        'hora_inicio',
        'hora_fim',
        'intervalo',
        'pausa_inicio',
        'pausa_fim',
        'modo_limite',
        'limite_consultas_dia',
    ];

    protected $casts = [
        'intervalo' => 'integer',
        'limite_consultas_dia' => 'integer',
    ];

    /**
     * Retorna a única configuração do sistema, ou cria uma em branco caso não exista.
     */
    public static function obterConfiguracao(): self
    {
        return self::firstOrCreate([], [
            'hora_inicio' => '08:00',
            'hora_fim' => '18:00',
            'intervalo' => 30,
            'modo_limite' => 'slots',
        ]);
    }

    /**
     * Calcula o total de slots disponíveis no dia com base no horário comercial,
     * intervalo de atendimento e pausa configurada.
     */
    public function calcularTotalSlots(): int
    {
        $converterParaMinutos = function (string $horaString): int {
            [$h, $m] = explode(':', $horaString);

            return ((int) $h * 60) + (int) $m;
        };

        $minutosInicio = $converterParaMinutos($this->hora_inicio ?: '08:00');
        $minutosFim = $converterParaMinutos($this->hora_fim ?: '18:00');
        $intervalo = (int) ($this->intervalo ?: 30);

        $totalSlots = 0;

        // Calcula minutos de pausa para excluir
        $pausaInicio = null;
        $pausaFim = null;
        if (! empty($this->pausa_inicio) && ! empty($this->pausa_fim)) {
            $pausaInicio = $converterParaMinutos($this->pausa_inicio);
            $pausaFim = $converterParaMinutos($this->pausa_fim);
        }

        for ($m = $minutosInicio; $m < $minutosFim; $m += $intervalo) {
            // Pula slots que caem dentro da pausa
            if ($pausaInicio !== null && $m >= $pausaInicio && $m < $pausaFim) {
                continue;
            }
            $totalSlots++;
        }

        return $totalSlots;
    }

    /**
     * Retorna o limite de consultas por dia conforme o modo configurado.
     */
    public function obterLimiteDia(): int
    {
        if ($this->modo_limite === 'manual' && $this->limite_consultas_dia !== null) {
            return $this->limite_consultas_dia;
        }

        return $this->calcularTotalSlots();
    }
}
