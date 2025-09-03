<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $id
 * @property string $nome
 * @property string $uf
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Paciente> $pacientes
 * @property-read int|null $pacientes_count
 * @method static \Database\Factories\CidadeFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cidade newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cidade newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cidade query()
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
