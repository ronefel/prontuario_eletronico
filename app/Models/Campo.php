<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $id
 * @property int|null $secao_id
 * @property string $titulo
 * @property string $tipo
 * @property string|null $unidade
 * @property int|null $casasdescimais
 * @property string|null $sim
 * @property string|null $nao
 * @property string|null $lista
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Secao|null $secao
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Campo newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Campo newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Campo query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Campo whereCasasdescimais($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Campo whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Campo whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Campo whereLista($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Campo whereNao($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Campo whereSecaoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Campo whereSim($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Campo whereTipo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Campo whereTitulo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Campo whereUnidade($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Campo whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Campo extends BaseModel
{
    use HasFactory;

    /**
     * Valores permitidos para o tipo do campo
     *
     * text = Texto curto;
     * textarea = Texto longo;
     * number = Numérico;
     * imc = Cálculo IMC;
     * radio = Verdadeiro ou falso;
     * list = Escolher de uma lista;
     * checkbox = Multipla escolha;
     * date = Data
     */
    public function secao()
    {
        return $this->belongsTo(Secao::class);
    }

    // Sobrescreve o método boot para adicionar validação customizada
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            // Valida se o valor do tipo está dentro da lista permitida
            if (! in_array($model->tipo, ['text', 'textarea', 'number', 'imc', 'radio', 'list', 'checkbox', 'date'])) {
                // Lança uma exceção caso o valor seja inválido
                throw new \InvalidArgumentException('Valor inválido para o tipo do campo.');
            }
        });
    }
}
