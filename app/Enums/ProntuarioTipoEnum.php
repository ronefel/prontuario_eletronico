<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ProntuarioTipoEnum: string implements HasLabel, HasColor
{

    case ATENDIMENTO = 'atendimento';
    case RECEITA = 'receita';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::ATENDIMENTO => 'Atendimento',
            self::RECEITA => 'Receita',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::ATENDIMENTO => 'primary',
            self::RECEITA => 'success',
        };
    }
}
