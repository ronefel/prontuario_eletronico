<?php

namespace App\Models;

use DateTime;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $id
 * @property string $nome
 * @property \Illuminate\Support\Carbon $nascimento
 * @property string $sexo
 * @property string|null $tiposanguineo
 * @property string $cpf
 * @property string|null $email
 * @property string|null $celular
 * @property string|null $cep
 * @property string|null $logradouro
 * @property string|null $numero
 * @property string|null $complemento
 * @property string|null $bairro
 * @property int|null $cidade_id
 * @property string|null $observacao
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Cidade|null $cidade
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Exame> $exames
 * @property-read int|null $exames_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Prontuario> $prontuarios
 * @property-read int|null $prontuarios_count
 * @method static \Database\Factories\PacienteFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Paciente newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Paciente newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Paciente query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Paciente whereBairro($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Paciente whereCelular($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Paciente whereCep($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Paciente whereCidadeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Paciente whereComplemento($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Paciente whereCpf($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Paciente whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Paciente whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Paciente whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Paciente whereLogradouro($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Paciente whereNascimento($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Paciente whereNome($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Paciente whereNumero($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Paciente whereObservacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Paciente whereSexo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Paciente whereTiposanguineo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Paciente whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Paciente extends BaseModel
{
    use HasFactory;

    protected $casts = [
        'nascimento' => 'date:Y-m-d',
    ];

    public function cidade()
    {
        return $this->belongsTo(Cidade::class);
    }

    public function prontuarios()
    {
        return $this->hasMany(Prontuario::class);
    }

    public function exames()
    {
        return $this->hasMany(Exame::class);
    }

    public function idade()
    {
        return Paciente::calcularIdade($this->nascimento);
    }

    public function sexo()
    {
        return $this->sexo === 'M' ? 'Masculino' : 'Feminino';
    }

    public static function calcularIdade(?string $nascimento): string
    {
        if ($nascimento) {
            $nascimento = new DateTime($nascimento);
            $hoje = new DateTime;
            $intervalo = $hoje->diff($nascimento);

            if ($intervalo->y >= 1) {
                return $intervalo->y.' Anos';
            } elseif ($intervalo->m >= 1) {
                return $intervalo->m.' Meses'.$intervalo->d - 1 .'Dias';
            } else {
                return $intervalo->d - 1 .' Dias';
            }
        }

        return '';
    }

    public function celularFormatado()
    {
        // Remove todos os caracteres que não sejam números
        $celular = preg_replace('/\D/', '', $this->celular);

        // Verifica se tem o tamanho correto de um número de celular (com DDD)
        if (strlen($celular) == 11) {
            // Formata para (XX) XXXXX-XXXX
            return '('.substr($celular, 0, 2).') '.substr($celular, 2, 5).'-'.substr($celular, 7);
        }

        // Caso não tenha o tamanho esperado, retorna o número sem formatação
        return $celular;
    }
}
