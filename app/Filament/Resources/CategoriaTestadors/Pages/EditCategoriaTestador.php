<?php

namespace App\Filament\Resources\CategoriaTestadors\Pages;

use App\Filament\Resources\CategoriaTestadors\CategoriaTestadorResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCategoriaTestador extends EditRecord
{
    protected static string $resource = CategoriaTestadorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->previousUrl ?? $this->getResource()::getUrl('index');
    }
}
