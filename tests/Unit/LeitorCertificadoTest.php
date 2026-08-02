<?php

namespace Tests\Unit;

use App\Models\ConfiguracaoNotaFiscal;
use App\Services\NotaFiscal\AssinadorXmlService;
use App\Services\NotaFiscal\LeitorCertificadoService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LeitorCertificadoTest extends TestCase
{
    use RefreshDatabase;

    public function test_leitura_e_assinatura_com_certificado_valido()
    {
        $leitor = new LeitorCertificadoService;
        $binOpenSsl = $leitor->obterCaminhoExecutavelOpenSsl() ?? 'openssl';
        $binEscapado = escapeshellarg($binOpenSsl);

        $senha = 'senha123';
        $caminhoChaveTemp = tempnam(sys_get_temp_dir(), 'testkey_');
        $caminhoCertTemp = tempnam(sys_get_temp_dir(), 'testcert_');
        $caminhoPfxTemp = tempnam(sys_get_temp_dir(), 'testpfx_');

        exec("{$binEscapado} req -x509 -newkey rsa:2048 -keyout ".escapeshellarg($caminhoChaveTemp).' -out '.escapeshellarg($caminhoCertTemp).' -days 365 -nodes -subj "/CN=Clinica Teste/OU=Certificado A1" 2>&1');
        exec("{$binEscapado} pkcs12 -export -out ".escapeshellarg($caminhoPfxTemp).' -inkey '.escapeshellarg($caminhoChaveTemp).' -in '.escapeshellarg($caminhoCertTemp)." -passout pass:{$senha} 2>&1");

        $conteudoPfx = file_get_contents($caminhoPfxTemp);
        Storage::disk('database')->put('teste.pfx', $conteudoPfx);

        $configuracao = (new ConfiguracaoNotaFiscal)->forceFill([
            'caminho_certificado' => 'teste.pfx',
            'senha_certificado' => $senha,
        ]);

        $dados = $leitor->obterDadosCertificado($configuracao);

        $this->assertNotEmpty($dados['pkey']);
        $this->assertNotEmpty($dados['cert']);
        $this->assertEquals('Clinica Teste', $dados['metadados']['titular']);
        $this->assertFalse($dados['metadados']['expirado']);

        // Testar assinatura do XML
        $xmlOriginal = '<InfDeclaracaoPrestacaoServico Id="RPS101"><Rps><Numero>101</Numero></Rps></InfDeclaracaoPrestacaoServico>';
        $assinador = new AssinadorXmlService($leitor);

        // Mock do método estático obterConfiguracaoAtiva
        ConfiguracaoNotaFiscal::query()->delete();
        ConfiguracaoNotaFiscal::create([
            'cnpj' => '00000000000191',
            'razao_social' => 'Clínica Médica Exemplo',
            'codigo_municipio_ibge' => '1100049',
            'uf' => 'RO',
            'regime_especial_tributacao' => 0,
            'optante_simples_nacional' => true,
            'incentivador_cultural' => false,
            'aliquota_iss' => 2.00,
            'caminho_certificado' => 'teste.pfx',
            'senha_certificado' => $senha,
            'serie_rps' => '1',
            'ultimo_numero_rps' => 0,
            'ambiente' => 'homologacao',
        ]);

        $xmlAssinado = $assinador->assinar($xmlOriginal, 'InfDeclaracaoPrestacaoServico', 'Id');

        $this->assertStringContainsString('<Signature', $xmlAssinado);
        $this->assertStringContainsString('<SignatureValue>', $xmlAssinado);
        $this->assertStringContainsString('<X509Certificate>', $xmlAssinado);

        @unlink($caminhoChaveTemp);
        @unlink($caminhoCertTemp);
        @unlink($caminhoPfxTemp);
    }

    public function test_leitura_certificado_com_ciphers_legados()
    {
        $leitor = new LeitorCertificadoService;
        $binOpenSsl = $leitor->obterCaminhoExecutavelOpenSsl() ?? 'openssl';
        $binEscapado = escapeshellarg($binOpenSsl);

        $senha = 'senha123';
        $caminhoChaveTemp = tempnam(sys_get_temp_dir(), 'testkey_leg_');
        $caminhoCertTemp = tempnam(sys_get_temp_dir(), 'testcert_leg_');
        $caminhoPfxTemp = tempnam(sys_get_temp_dir(), 'testpfx_leg_');

        exec("{$binEscapado} req -x509 -newkey rsa:2048 -keyout ".escapeshellarg($caminhoChaveTemp).' -out '.escapeshellarg($caminhoCertTemp).' -days 365 -nodes -subj "/CN=Clinica Legada/OU=Certificado A1" 2>&1');
        exec("{$binEscapado} pkcs12 -export -legacy -out ".escapeshellarg($caminhoPfxTemp).' -inkey '.escapeshellarg($caminhoChaveTemp).' -in '.escapeshellarg($caminhoCertTemp)." -passout pass:{$senha} -keypbe pbeWithSHA1And3-KeyTripleDES-CBC -certpbe pbeWithSHA1And40BitRC2-CBC 2>&1");

        $conteudoPfx = file_get_contents($caminhoPfxTemp);
        Storage::disk('database')->put('teste_legado.pfx', $conteudoPfx);

        $configuracao = (new ConfiguracaoNotaFiscal)->forceFill([
            'caminho_certificado' => 'teste_legado.pfx',
            'senha_certificado' => $senha,
        ]);

        $dados = $leitor->obterDadosCertificado($configuracao);

        $this->assertNotEmpty($dados['pkey']);
        $this->assertNotEmpty($dados['cert']);
        $this->assertEquals('Clinica Legada', $dados['metadados']['titular']);

        @unlink($caminhoChaveTemp);
        @unlink($caminhoCertTemp);
        @unlink($caminhoPfxTemp);
    }

    public function test_falha_com_senha_incorreta()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Falha ao ler o certificado digital A1. Verifique a senha informada');

        $leitor = new LeitorCertificadoService;
        $binOpenSsl = $leitor->obterCaminhoExecutavelOpenSsl() ?? 'openssl';
        $binEscapado = escapeshellarg($binOpenSsl);

        $senhaCorreta = 'senha123';
        $senhaIncorreta = 'errada';

        $caminhoChaveTemp = tempnam(sys_get_temp_dir(), 'testkey_err_');
        $caminhoCertTemp = tempnam(sys_get_temp_dir(), 'testcert_err_');
        $caminhoPfxTemp = tempnam(sys_get_temp_dir(), 'testpfx_err_');

        exec("{$binEscapado} req -x509 -newkey rsa:2048 -keyout ".escapeshellarg($caminhoChaveTemp).' -out '.escapeshellarg($caminhoCertTemp).' -days 365 -nodes -subj "/CN=TestErr" 2>&1');
        exec("{$binEscapado} pkcs12 -export -out ".escapeshellarg($caminhoPfxTemp).' -inkey '.escapeshellarg($caminhoChaveTemp).' -in '.escapeshellarg($caminhoCertTemp)." -passout pass:{$senhaCorreta} 2>&1");

        $conteudoPfx = file_get_contents($caminhoPfxTemp);
        Storage::disk('database')->put('teste_errado.pfx', $conteudoPfx);

        $configuracao = (new ConfiguracaoNotaFiscal)->forceFill([
            'caminho_certificado' => 'teste_errado.pfx',
            'senha_certificado' => $senhaIncorreta,
        ]);

        try {
            $leitor->obterDadosCertificado($configuracao);
        } finally {
            @unlink($caminhoChaveTemp);
            @unlink($caminhoCertTemp);
            @unlink($caminhoPfxTemp);
        }
    }
}
