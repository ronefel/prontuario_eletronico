<?php

namespace App\Filament\Resources\NotasFiscais\Pages;

use App\Filament\Resources\NotasFiscais\NotaFiscalResource;
use Filament\Resources\Pages\CreateRecord;

class CreateNotaFiscal extends CreateRecord
{
    protected static string $resource = NotaFiscalResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();
        $data['status'] = 'rascunho';
        $data['data_emissao_rps'] = now();

        return $data;
    }
}
