<?php

namespace App\Models;

use DateTime;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Paciente extends Model
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
            $hoje = new DateTime();
            $intervalo = $hoje->diff($nascimento);

            if ($intervalo->y >= 1) {
                return $intervalo->y . ' Anos';
            } else if ($intervalo->m >= 1) {
                return $intervalo->m . ' Meses' . $intervalo->d - 1 . 'Dias';
            } else {
                return $intervalo->d - 1 . ' Dias';
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
            return '(' . substr($celular, 0, 2) . ') ' . substr($celular, 2, 5) . '-' . substr($celular, 7);
        }

        // Caso não tenha o tamanho esperado, retorna o número sem formatação
        return $celular;
    }
}
