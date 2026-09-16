<?php

namespace Tests\Feature;

use App\Models\Tenant;
use Livewire\Mechanisms\HandleRequests\EndpointResolver;
use Tests\TestCase;

class TenancyIntegrationTest extends TestCase
{
    public function test_dominio_central_redireciona_raiz_para_painel_central(): void
    {
        $response = $this->get('http://central.test/');

        $response->assertRedirect('/central');
    }

    public function test_painel_central_login_acessivel_em_dominio_central(): void
    {
        $response = $this->get('http://central.test/central/login');

        $response->assertStatus(200);
    }

    public function test_painel_admin_bloqueado_em_dominio_central(): void
    {
        $response = $this->get('http://central.test/admin/login');

        $response->assertStatus(404);
    }

    public function test_criacao_de_tenant_e_acesso_via_subdominio(): void
    {
        $tenant = Tenant::create([
            'id' => 'clinica-demo',
            'nome' => 'Clínica Demonstração',
            'cnpj' => '12.345.678/0001-90',
            'email' => 'contato@clinicademo.com',
        ]);

        $tenant->domains()->create([
            'domain' => 'clinica-demo',
        ]);

        $response = $this->get('http://clinica-demo.central.test/admin/login');

        $response->assertStatus(200);

        // Testa se o endpoint do Livewire inicializa o tenant no subdomínio
        $livewireResponse = $this->post('http://clinica-demo.central.test'.EndpointResolver::updatePath(), [], [
            'X-Livewire' => 'true',
        ]);

        $this->assertNotNull(tenant());
        $this->assertEquals('clinica-demo', tenant('id'));

        $tenant->delete();
    }

    public function test_livewire_update_em_dominio_central_permanece_sem_tenant(): void
    {
        $livewireResponse = $this->post('http://central.test'.EndpointResolver::updatePath(), [], [
            'X-Livewire' => 'true',
        ]);

        $this->assertNull(tenant());
    }
}
