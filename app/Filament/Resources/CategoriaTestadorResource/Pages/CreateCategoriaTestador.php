<?php

namespace App\Filament\Resources\CategoriaTestadorResource\Pages;

use App\Filament\Resources\CategoriaTestadorResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCategoriaTestador extends CreateRecord
{
    protected static string $resource = CategoriaTestadorResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->previousUrl ?? $this->getResource()::getUrl('index');
    }
}
