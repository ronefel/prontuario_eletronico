<?php

namespace App\Filament\Resources\TratamentoResource\Pages;

use App\Filament\Resources\TratamentoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTratamento extends EditRecord
{
    protected static string $resource = TratamentoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
