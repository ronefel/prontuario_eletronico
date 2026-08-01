# Guia de Configuração e Uso do Certificado Digital A1

Este documento descreve o funcionamento, a arquitetura e os passos de configuração do **Certificado Digital A1 (.pfx / .p12)** na aplicação, cobrindo ambientes de desenvolvimento local e produção no **Dokploy com Railpack (v0.15.4+)**.

---

## 📋 Visão Geral

A emissão de Nota Fiscal de Serviço Eletrônica (NFS-e) e a assinatura de XMLs (ABRASF v2.02) exigem um **Certificado Digital A1**. 

Muitos certificados A1 emitidos no Brasil por Autoridades Certificadoras (Certisign, Serasa, Valid, Soluti, BB, etc.) utilizam algoritmos de criptografia legados (como `RC2-40-CBC`, `TripleDES` e `SHA1 PBE`).

Com a chegada do **OpenSSL 3.0+** (padrão no PHP 8.1, 8.2 e 8.3), esses algoritmos legados foram desativados por padrão no PHP nativo (`openssl_pkcs12_read()`), gerando o erro:
> `error:0308010C:digital envelope routines::unsupported`

---

## 🛠️ Como o Sistema Resolve Isso

O serviço `App\Services\NotaFiscal\LeitorCertificadoService` implementa uma estratégia de leitura em duas etapas:

1. **Leitura Nativa PHP**: Tenta ler o certificado via `openssl_pkcs12_read()`.
2. **Fallback Automático CLI (-legacy)**: Caso a função nativa falhe por incompatibilidade de ciphers legados, o sistema:
   - Localiza automaticamente o binário do `openssl` CLI no sistema operacional.
   - Executa a extração usando as flags `-legacy` e `-provider legacy -provider default`.
   - Isola e valida os blocos PEM da chave privada e do certificado X509 sem intervenção do usuário.

---

## 💻 1. Ambiente Local (Desenvolvimento)

### Windows
O sistema busca o executável do OpenSSL automaticamente nos seguintes locais (por ordem de prioridade):
1. Variável de ambiente `OPENSSL_PATH` no `.env` (se definida).
2. Executável no `PATH` do sistema (`where.exe openssl`).
3. Instalação do Git para Windows: `C:\Program Files\Git\mingw64\bin\openssl.exe` ou `C:\Program Files\Git\usr\bin\openssl.exe`.
4. Instalações do Laragon (`C:\laragon\bin\openssl\...\openssl.exe`), XAMPP ou pasta do PHP.

> 💡 **Nota:** Se você utiliza Git para Windows, Laragon ou XAMPP, **nenhuma configuração manual é necessária**.

### Linux / macOS
O sistema busca o binário `openssl` via `which openssl` ou nos caminhos padrão `/usr/bin/openssl`, `/usr/local/bin/openssl` e `/opt/homebrew/bin/openssl`.

---

## 🚀 2. Ambiente de Produção (Dokploy com Railpack v0.15.4 / Nixpacks)

Ao implantar a aplicação no **Dokploy** utilizando o construtor **Railpack (v0.15.4)** ou **Nixpacks**, siga as orientações abaixo para garantir o pleno funcionamento do Certificado A1.

### A. Variáveis de Ambiente do Railpack (v0.15.4) no Dokploy

No **Dokploy**, ao configurar o deployment via **Railpack**, defina as seguintes variáveis de ambiente na aba **Environment Variables** da aplicação:

1. **`RAILPACK_PHP_EXTENSIONS`** (Extensões do PHP):  
   Garante a instalação e ativação da extensão PHP `openssl` juntamente com as demais extensões necessárias pelo Laravel/Filament.
   ```ini
   RAILPACK_PHP_EXTENSIONS="openssl, pdo_pgsql, pgsql, gd, zip, mbstring, xml, curl, bcmath"
   ```

2. **`RAILPACK_APT_PACKAGES`** (Pacotes de Sistema Debian/Ubuntu):  
   Instala o utilitário de linha de comando `openssl` (`/usr/bin/openssl`), essencial para o suporte a certificados A1 com criptografia legada.
   ```ini
   RAILPACK_APT_PACKAGES="openssl"
   ```

### B. Configuração Alternativa via `nixpacks.toml` / `railpack.json`
Caso prefira declarar as dependências por arquivo de configuração no repositório:

- **`nixpacks.toml`**:
  ```toml
  [providers]
  providers = ["php", "node"]

  [pkgs]
  apt = ["openssl"]
  ```

- **`Dockerfile` customizado**:
  ```dockerfile
  RUN apt-get update && apt-get install -y openssl
  ```

### C. Variável de Caminho `OPENSSL_PATH` (Opcional)
Como o Railpack instala o binário do OpenSSL no caminho padrão do Linux (`/usr/bin/openssl`), a aplicação o detectará **automaticamente**. Se necessário forçar um caminho alternativo:
```ini
OPENSSL_PATH=/usr/bin/openssl
```

### D. Permissão no Diretório Temporário
O processamento do certificado A1 gera arquivos temporários seguros em `sys_get_temp_dir()` (`/tmp` no Linux) para conversão e requisições mTLS cURL. Certifique-se de que o container possua acesso de escrita na pasta temporária.

---

## 🔍 3. Como Testar o Certificado A1 no Sistema

1. Acesse o painel administrativo em **Sistema > Config. Nota Fiscal**.
2. No campo **Arquivo do Certificado A1**, faça o upload do seu arquivo `.pfx` ou `.p12`.
3. Preencha o campo **Senha do Certificado Digital**.
4. Clique no botão **Testar Certificado Digital** no rodapé da página.
5. Se o certificado for válido, uma notificação exibirá o **Titular**, **Emissor** e **Data de Validade**.

---

## 🛠️ Solução de Problemas (Troubleshooting)

| Erro / Sintoma | Causa Provável | Solução |
| :--- | :--- | :--- |
| `error:0308010C:digital envelope routines::unsupported` | Binário do `openssl` CLI não encontrado no sistema. | Verifique se o `openssl` está instalado e adicione `OPENSSL_PATH="C:\caminho\openssl.exe"` (Windows) ou `OPENSSL_PATH="/usr/bin/openssl"` (Linux) no `.env`. |
| `Falha ao ler o certificado digital A1. Verifique a senha informada.` | Senha do certificado incorreta ou arquivo corrompido. | Confirme a senha digitada ou tente exportar novamente o certificado `.pfx`. |
| `Não foi possível criar um arquivo temporário...` | Sem permissão de escrita no diretório temporário do SO. | Verifique as permissões da pasta temporária do sistema (`/tmp` no Linux ou `C:\Users\...\AppData\Local\Temp` no Windows). |

---

## 🧪 Executando Testes Automatizados

Para validar o funcionamento do leitor de certificados em novos ambientes de integração contínua (CI/CD) ou staging, execute:

```bash
php artisan test --filter=LeitorCertificadoTest
```
