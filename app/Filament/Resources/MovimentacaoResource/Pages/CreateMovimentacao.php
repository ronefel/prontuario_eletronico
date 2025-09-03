<?php

namespace App\Filament\Resources\MovimentacaoResource\Pages;

use App\Filament\Resources\MovimentacaoResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMovimentacao extends CreateRecord
{
    protected static string $resource = MovimentacaoResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->previousUrl ?? $this->getResource()::getUrl('index');
    }
}
