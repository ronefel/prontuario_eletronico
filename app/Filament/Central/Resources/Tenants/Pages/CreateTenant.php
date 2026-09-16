<?php

namespace App\Filament\Central\Resources\Tenants\Pages;

use App\Filament\Central\Resources\Tenants\TenantResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTenant extends CreateRecord
{
    protected static string $resource = TenantResource::class;

    protected function afterCreate(): void
    {
        $tenant = $this->record;

        if (! $tenant->domains()->where('domain', $tenant->id)->exists()) {
            $tenant->domains()->create([
                'domain' => $tenant->id,
            ]);
        }
    }
}
