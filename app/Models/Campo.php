<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

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
            if (!in_array($model->tipo, ['text', 'textarea', 'number', 'imc', 'radio', 'list', 'checkbox', 'date'])) {
                // Lança uma exceção caso o valor seja inválido
                throw new \InvalidArgumentException('Valor inválido para o tipo do campo.');
            }
        });
    }
}
