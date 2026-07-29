<?php

namespace App\Services\NotaFiscal;

use App\Models\ConfiguracaoNotaFiscal;
use Exception;
use Illuminate\Support\Facades\Storage;

class LeitorCertificadoService
{
    /**
     * Obtém os dados do certificado digital A1 (chave privada, certificado X509 e metadados).
     *
     * @param  ConfiguracaoNotaFiscal|null  $configuracao  Instância da configuração da nota fiscal.
     * @return array{pkey: string, cert: string, metadados: array{titular: string, validade: string, expirado: bool, emissor: string}}
     *
     * @throws Exception Se o arquivo não for encontrado, estiver vazio ou a senha for inválida.
     */
    public function obterDadosCertificado(?ConfiguracaoNotaFiscal $configuracao = null): array
    {
        $configuracao = $configuracao ?? ConfiguracaoNotaFiscal::obterConfiguracaoAtiva();

        if (! $configuracao || ! $configuracao->caminho_certificado) {
            throw new Exception('Certificado digital não configurado.');
        }

        $conteudoCertificado = $this->obterConteudoArquivoCertificado($configuracao->caminho_certificado);

        if (empty($conteudoCertificado)) {
            throw new Exception("Arquivo de certificado digital não encontrado ou vazio: {$configuracao->caminho_certificado}");
        }

        $senha = $configuracao->senha_certificado_descriptografada ?? '';

        $dadosCertificado = $this->lerCertificadoPkcs12($conteudoCertificado, $senha);
        $metadados = $this->obterMetadadosCertificado($dadosCertificado['cert']);

        return [
            'pkey' => $dadosCertificado['pkey'],
            'cert' => $dadosCertificado['cert'],
            'metadados' => $metadados,
        ];
    }

    /**
     * Recupera o conteúdo binário do arquivo do certificado testando diferentes discos e caminhos.
     */
    public function obterConteudoArquivoCertificado(string $caminhoCertificado): ?string
    {
        // 1. Tentar no disco 'database' (padrão de upload do formulário Filament)
        try {
            if (Storage::disk('database')->exists($caminhoCertificado)) {
                return Storage::disk('database')->get($caminhoCertificado);
            }
        } catch (Exception $e) {
            // Ignorar exceção de disco inexistente e continuar para o próximo fallback
        }

        // 2. Tentar no disco padrão (local)
        try {
            if (Storage::exists($caminhoCertificado)) {
                return Storage::get($caminhoCertificado);
            }
        } catch (Exception $e) {
            // Ignorar exceção de disco inexistente e continuar para o próximo fallback
        }

        // 3. Tentar caminhos absolutos e locais do Laravel 11
        $caminhosPossiveis = [
            storage_path('app/private/'.ltrim($caminhoCertificado, '/')),
            storage_path('app/'.ltrim($caminhoCertificado, '/')),
            $caminhoCertificado,
        ];

        foreach ($caminhosPossiveis as $caminho) {
            if (file_exists($caminho) && is_readable($caminho)) {
                return file_get_contents($caminho);
            }
        }

        return null;
    }

    /**
     * Lê o certificado PKCS#12 (.pfx/.p12) via PHP nativo com fallback para OpenSSL CLI (-legacy).
     *
     * @return array{pkey: string, cert: string}
     *
     * @throws Exception
     */
    protected function lerCertificadoPkcs12(string $conteudoCertificado, string $senha): array
    {
        // Limpar mensagens de erro prévias do buffer do OpenSSL
        while (openssl_error_string() !== false) {
            // Apenas consome a fila de erros
        }

        $dadosCertificado = [];

        // 1. Tentar leitura nativa do PHP
        if (openssl_pkcs12_read($conteudoCertificado, $dadosCertificado, $senha)) {
            if (! empty($dadosCertificado['pkey']) && ! empty($dadosCertificado['cert'])) {
                return [
                    'pkey' => $dadosCertificado['pkey'],
                    'cert' => $dadosCertificado['cert'],
                ];
            }
        }

        $errosOpenSsl = [];
        while ($mensagemErro = openssl_error_string()) {
            $errosOpenSsl[] = $mensagemErro;
        }

        // 2. Fallback via CLI OpenSSL com suporte a algoritmos legados (-legacy)
        $arquivoTempCertificado = tempnam(sys_get_temp_dir(), 'cert_a1_');

        if ($arquivoTempCertificado === false) {
            throw new Exception('Não foi possível criar um arquivo temporário para processamento do certificado digital.');
        }

        try {
            file_put_contents($arquivoTempCertificado, $conteudoCertificado);

            $senhaEscapada = escapeshellarg($senha);
            $caminhoEscapado = escapeshellarg($arquivoTempCertificado);
            $comando = "openssl pkcs12 -in {$caminhoEscapado} -passin pass:{$senhaEscapada} -nodes -legacy 2>&1";

            $saidaComando = shell_exec($comando);

            if ($saidaComando && str_contains($saidaComando, 'BEGIN PRIVATE KEY') && str_contains($saidaComando, 'BEGIN CERTIFICATE')) {
                $recursoChavePrivada = openssl_pkey_get_private($saidaComando);
                $recursoCertificado = openssl_x509_read($saidaComando);

                $chavePrivada = '';
                $certificadoX509 = '';

                if ($recursoChavePrivada) {
                    openssl_pkey_export($recursoChavePrivada, $chavePrivada);
                }

                if ($recursoCertificado) {
                    openssl_x509_export($recursoCertificado, $certificadoX509);
                }

                if (! empty($chavePrivada) && ! empty($certificadoX509)) {
                    return [
                        'pkey' => $chavePrivada,
                        'cert' => $certificadoX509,
                    ];
                }
            }
        } finally {
            if (file_exists($arquivoTempCertificado)) {
                @unlink($arquivoTempCertificado);
            }
        }

        $detalhesErroTexto = ! empty($errosOpenSsl)
            ? implode(' | ', $errosOpenSsl)
            : 'Certificado inválido, senha incorreta ou arquivo corrompido.';

        throw new Exception("Falha ao ler o certificado digital A1. Verifique a senha informada. (Detalhe técnico: {$detalhesErroTexto})");
    }

    /**
     * Extrai metadados do certificado X509 (titular, validade e emissor).
     *
     * @return array{titular: string, validade: string, expirado: bool, emissor: string}
     */
    public function obterMetadadosCertificado(string $certificadoX509): array
    {
        $informacoesCertificado = openssl_x509_parse($certificadoX509);

        if (! $informacoesCertificado) {
            return [
                'titular' => 'Desconhecido',
                'validade' => 'Desconhecida',
                'expirado' => true,
                'emissor' => 'Desconhecido',
            ];
        }

        $titular = $informacoesCertificado['subject']['CN']
            ?? $informacoesCertificado['subject']['O']
            ?? 'Certificado A1';

        $emissor = $informacoesCertificado['issuer']['CN']
            ?? $informacoesCertificado['issuer']['O']
            ?? 'Autoridade Certificadora';

        $timestampValidade = $informacoesCertificado['validTo_time_t'] ?? 0;
        $dataValidadeFormatada = $timestampValidade > 0
            ? date('d/m/Y H:i:s', $timestampValidade)
            : 'Desconhecida';

        $expirado = $timestampValidade < time();

        return [
            'titular' => $titular,
            'validade' => $dataValidadeFormatada,
            'expirado' => $expirado,
            'emissor' => $emissor,
        ];
    }

    /**
     * Cria um arquivo temporário no formato PEM (Chave Privada + Certificado X509) para uso seguro no cURL mTLS.
     */
    public function criarArquivoPemTemporario(?ConfiguracaoNotaFiscal $configuracao = null): ?string
    {
        try {
            $dados = $this->obterDadosCertificado($configuracao);
            $caminhoArquivoPem = tempnam(sys_get_temp_dir(), 'cert_pem_');

            if ($caminhoArquivoPem === false) {
                return null;
            }

            $conteudoPem = $dados['pkey']."\n".$dados['cert'];
            file_put_contents($caminhoArquivoPem, $conteudoPem);

            return $caminhoArquivoPem;
        } catch (Exception $e) {
            return null;
        }
    }
}
