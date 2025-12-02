<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property \Illuminate\Database\Eloquent\Collection<int, \App\Models\Aplicacao> $aplicacoes
 * @property \App\Models\Paciente|null $paciente
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tratamento newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tratamento newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tratamento query()
 * @mixin \Eloquent
 */
class Tratamento extends BaseModel
{
    use SoftDeletes;

    protected $table = 'tratamentos';

    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }

    public function aplicacoes()
    {
        return $this->hasMany(Aplicacao::class);
    }
}
