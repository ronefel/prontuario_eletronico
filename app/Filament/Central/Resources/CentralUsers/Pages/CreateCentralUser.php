<?php

namespace App\Filament\Central\Resources\CentralUsers\Pages;

use App\Filament\Central\Resources\CentralUsers\CentralUserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCentralUser extends CreateRecord
{
    protected static string $resource = CentralUserResource::class;
}
