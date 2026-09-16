<?php

namespace Tests;

use App\Models\Tenant;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\URL;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate');
        $this->artisan('migrate', [
            '--path' => 'database/migrations/tenant',
            '--realpath' => false,
        ]);
    }

    public function criarTenantDeTeste(string $id = 'clinica-teste'): Tenant
    {
        $tenant = Tenant::find($id);

        if (! $tenant) {
            $tenant = Tenant::create([
                'id' => $id,
                'nome' => 'Clínica Teste',
                'ativo' => true,
            ]);

            $tenant->domains()->create(['domain' => $id]);
        }

        $this->withServerVariables(['HTTP_HOST' => "{$id}.localhost"]);
        config(['app.url' => "http://{$id}.localhost"]);
        URL::forceRootUrl("http://{$id}.localhost");

        return $tenant;
    }
}
