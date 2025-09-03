<?php

namespace App\Models;

/**
 * @property string $key
 * @property string $label
 * @property string|null $value
 * @property array<array-key, mixed>|null $attributes
 * @property string $type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereAttributes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereValue($value)
 * @mixin \Eloquent
 */
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

    const BIORRESSONANCIA_TEXTO_INICIAL = 'biorressonancia_texto_inicial';

    const BIORRESSONANCIA_TEXTO_FINAL = 'biorressonancia_texto_final';

    /**
     * Retorna todos os settings em um array chave-valor.
     * Pode ser chamado de qualquer lugar para carregar os settings de forma otimizada.
     */
    public static function getAllSettings()
    {
        return self::all()->pluck('value', 'key')->toArray();
    }
}
