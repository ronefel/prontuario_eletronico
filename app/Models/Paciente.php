<?php

namespace App\Models;

use DateTime;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Paciente extends Model
{
    use HasFactory;

    public function cidade()
    {
        return $this->belongsTo(Cidade::class);
    }

    public static function calcularIdade(?string $nascimento): string
    {
        if ($nascimento) {
            $nascimento = new DateTime($nascimento);
            $hoje = new DateTime();
            $intervalo = $hoje->diff($nascimento);

            if ($intervalo->y >= 1) {
                return $intervalo->y . 'A';
            } else if ($intervalo->m >= 1) {
                return $intervalo->m . 'M' . $intervalo->d - 1 . 'D';
            } else {
                return $intervalo->d - 1 . 'D';
            }
        }

        return '';
    }
}
