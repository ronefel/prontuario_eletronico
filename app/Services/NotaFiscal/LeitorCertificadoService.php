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
     * Encontra o executável do OpenSSL no sistema (suportando Windows, Linux e macOS).
     */
    public function obterCaminhoExecutavelOpenSsl(): ?string
    {
        $caminhoEnv = env('OPENSSL_PATH');
        if ($caminhoEnv && file_exists($caminhoEnv)) {
            return $caminhoEnv;
        }

        // Tentar executável no PATH do sistema
        $comandoTeste = PHP_OS_FAMILY === 'Windows' ? 'where.exe openssl 2>NUL' : 'which openssl 2>/dev/null';
        $saidaPath = trim((string) shell_exec($comandoTeste));
        if (! empty($saidaPath)) {
            $linhas = explode("\n", str_replace("\r", '', $saidaPath));
            $primeiro = trim($linhas[0]);
            if (file_exists($primeiro)) {
                return $primeiro;
            }
        }

        // Se estiver no Windows, testar locais comuns de instalação (ex: Git para Windows, Laragon, XAMPP)
        if (PHP_OS_FAMILY === 'Windows') {
            $locaisWindows = [
                'C:\\Program Files\\Git\\mingw64\\bin\\openssl.exe',
                'C:\\Program Files\\Git\\usr\\bin\\openssl.exe',
                'C:\\Program Files\\OpenSSL-Win64\\bin\\openssl.exe',
                'C:\\Program Files\\OpenSSL-Win32\\bin\\openssl.exe',
                'C:\\Program Files (x86)\\OpenSSL-Win32\\bin\\openssl.exe',
                'C:\\xampp\\apache\\bin\\openssl.exe',
                'C:\\php\\openssl.exe',
            ];

            foreach ($locaisWindows as $caminho) {
                if (file_exists($caminho)) {
                    return $caminho;
                }
            }

            // Buscar em instalações do Laragon
            $padraoLaragon = glob('C:\\laragon\\bin\\openssl\\*\\openssl.exe');
            if (! empty($padraoLaragon) && file_exists($padraoLaragon[0])) {
                return $padraoLaragon[0];
            }
        } else {
            // Linux / macOS
            $locaisUnix = [
                '/usr/bin/openssl',
                '/usr/local/bin/openssl',
                '/opt/homebrew/bin/openssl',
            ];

            foreach ($locaisUnix as $caminho) {
                if (file_exists($caminho)) {
                    return $caminho;
                }
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
        if (@openssl_pkcs12_read($conteudoCertificado, $dadosCertificado, $senha)) {
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

        // 2. Fallback via CLI OpenSSL com suporte a algoritmos legados (-legacy / -provider legacy)
        $caminhoOpenSsl = $this->obterCaminhoExecutavelOpenSsl();

        if ($caminhoOpenSsl) {
            $arquivoTempCertificado = tempnam(sys_get_temp_dir(), 'cert_a1_');

            if ($arquivoTempCertificado !== false) {
                try {
                    file_put_contents($arquivoTempCertificado, $conteudoCertificado);

                    $senhaEscapada = escapeshellarg($senha);
                    $caminhoEscapado = escapeshellarg($arquivoTempCertificado);
                    $binEscapado = escapeshellarg($caminhoOpenSsl);

                    // Tentar variações de comandos OpenSSL CLI com flags para criptografia legada
                    $comandos = [
                        "{$binEscapado} pkcs12 -in {$caminhoEscapado} -passin pass:{$senhaEscapada} -nodes -legacy 2>&1",
                        "{$binEscapado} pkcs12 -in {$caminhoEscapado} -passin pass:{$senhaEscapada} -nodes -provider legacy -provider default 2>&1",
                    ];

                    foreach ($comandos as $comando) {
                        $saidaComando = shell_exec($comando);

                        if ($saidaComando &&
                            preg_match('/-----BEGIN (?:RSA )?PRIVATE KEY-----[\s\S]+?-----END (?:RSA )?PRIVATE KEY-----/', $saidaComando, $matchesChave) &&
                            preg_match('/-----BEGIN CERTIFICATE-----[\s\S]+?-----END CERTIFICATE-----/', $saidaComando, $matchesCert)
                        ) {
                            $chavePrivadaPem = trim($matchesChave[0]);
                            $certificadoPem = trim($matchesCert[0]);

                            $recursoChavePrivada = openssl_pkey_get_private($chavePrivadaPem);
                            $recursoCertificado = openssl_x509_read($certificadoPem);

                            if ($recursoChavePrivada !== false && $recursoCertificado !== false) {
                                return [
                                    'pkey' => $chavePrivadaPem,
                                    'cert' => $certificadoPem,
                                ];
                            }
                        }
                    }
                } finally {
                    if (file_exists($arquivoTempCertificado)) {
                        @unlink($arquivoTempCertificado);
                    }
                }
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
