<?php

namespace App\Models;

use App\Casts\DatetimeWithTimezone;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $id
 * @property int $paciente_id
 * @property mixed $data
 * @property string|null $tratamento
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Paciente $paciente
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Testador> $testadores
 * @property-read int|null $testadores_count
 * @method static \Database\Factories\ExameFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Exame newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Exame newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Exame query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Exame whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Exame whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Exame whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Exame wherePacienteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Exame whereTratamento($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Exame whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Exame extends BaseModel
{
    use HasFactory;

    protected $casts = [
        'data' => DatetimeWithTimezone::class,
    ];

    public function testadores()
    {
        return $this->belongsToMany(Testador::class, 'exame_testador');
    }

    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }
}
