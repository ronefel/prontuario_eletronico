<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $nome
 * @property string $uf
 * @property string|null $codigo_ibge
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Collection<int, Paciente> $pacientes
 * @method static \Database\Factories\CidadeFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cidade newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cidade newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cidade query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cidade whereCodigoIbge($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cidade whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cidade whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cidade whereNome($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cidade whereUf($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cidade whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Cidade extends BaseModel
{
    use HasFactory;

    public function cidadeUf()
    {
        return $this->nome.' - '.$this->uf;
    }

    public function pacientes()
    {
        return $this->hasMany(Paciente::class);
    }
}
