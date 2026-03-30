<?php

namespace App\Filament\Resources\Movimentacoes\Pages;

use App\Filament\Resources\Movimentacoes\MovimentacaoResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMovimentacao extends CreateRecord
{
    protected static string $resource = MovimentacaoResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->previousUrl ?? $this->getResource()::getUrl('index');
    }
}
