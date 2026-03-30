<?php

namespace App\Filament\Resources\CategoriaTestadors\Pages;

use App\Filament\Resources\CategoriaTestadors\CategoriaTestadorResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCategoriaTestador extends CreateRecord
{
    protected static string $resource = CategoriaTestadorResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->previousUrl ?? $this->getResource()::getUrl('index');
    }
}
