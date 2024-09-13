<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Setting extends BaseModel
{
    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $fillable = [
        'key',
        'label',
        'value',
        'type',
        'attributes',
    ];

    protected $casts = [
        'attributes' => 'array',
    ];

    const CABECALHO = 'cabecalho';
    const RODAPE = 'rodape';
    const MARGEM_SUPERIOR = 'margem_superior';
    const MARGEM_INFERIOR = 'margem_inferior';
    const MARGEM_ESQUERDA = 'margem_esquerda';
    const MARGEM_DIREITA = 'margem_direita';
    const ALTURA_CABECALHO = 'altura_cabecalho';
    const ALTURA_RODAPE = 'altura_rodape';

    /**
     * Retorna todos os settings em um array chave-valor.
     * Pode ser chamado de qualquer lugar para carregar os settings de forma otimizada.
     */
    public static function getAllSettings()
    {
        return self::all()->pluck('value', 'key')->toArray();
    }
}
