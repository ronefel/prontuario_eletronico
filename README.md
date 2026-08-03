# 🏥 Prontuário Eletrônico & Gestão de NFS-e

Sistema completo de **Prontuário Eletrônico Médico** com integração nativa para **Emissão de Nota Fiscal de Serviço Eletrônica (NFS-e)** no padrão ABRASF v2.02 e suporte avançado a **Certificados Digitais A1**.

---

## 🚀 Principais Recursos

- **📋 Prontuário Eletrônico Médico:**
  - Gestão completa de pacientes, histórico clínico e validações para emissão fiscal.
  - Exportação de relatórios e documentos em PDF (via mPDF) e planilhas Excel.

- **🧾 Emissão e Gestão de NFS-e (ABRASF v2.02):**
  - Geração automatizada de XML de Declaração de Prestação de Serviço (RPS).
  - Assinatura digital XML-DSig conforme padrões ICP-Brasil.
  - Suporte a cancelamento, substituição e consulta de notas fiscais.

- **🔐 Suporte Inteligente a Certificado Digital A1:**
  - Leitura nativa e fallback automático para certificados A1 com criptografia legada (`RC2-40-CBC`, `3DES`, `SHA1 PBE`).
  - Suporte total a OpenSSL 3.0+ (PHP 8.3).
  - Teste de certificado integrado diretamente no painel.

- **⚡ Painel de Controle Moderno (FilamentPHP v5):**
  - Interface rica, responsiva e performática construída sobre Filament v5.

---

## 🛠️ Tecnologias Utilizadas

- **Linguagem:** PHP 8.3+
- **Framework:** Laravel 13
- **Painel Administrativo:** FilamentPHP v5
- **Banco de Dados:** PostgreSQL (pgsql)
- **Frontend / Bundler:** Vite, TailwindCSS
- **Geração de PDF:** mPDF
- **Exportação de Dados:** Maatwebsite Excel
- **Testes:** PHPUnit / PestPHP

---

## 💻 Requisitos de Ambiente

- **PHP** >= 8.3 com extensões: `openssl`, `pdo_pgsql`, `mbstring`, `gd`, `xml`, `zip`.
- **PostgreSQL** >= 14
- **Node.js** >= 18 e **npm**
- **Composer** >= 2.6
- **OpenSSL CLI** instalado no sistema (necessário para compatibilidade com certificados A1 legados).

---

## ⚙️ Instalação e Configuração Local

### 1. Clonar o Repositório
```bash
git clone https://github.com/usuario/prontuario_eletronico.git
cd prontuario_eletronico
```

### 2. Instalar Dependências do PHP e Node.js
```bash
composer install
npm install
```

### 3. Configurar o Arquivo `.env`
Copie o arquivo de exemplo e ajuste as credenciais do banco de dados PostgreSQL:
```bash
cp .env.example .env
php artisan key:generate
```

Ajuste as configurações no `.env`:
```ini
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=prontuario_eletronico
DB_USERNAME=seu_usuario
DB_PASSWORD=sua_senha
```

### 4. Executar Migrações e Seeders
```bash
php artisan migrate --seed
```

### 5. Iniciar o Ambiente de Desenvolvimento
Execute o comando unificado que inicia o servidor Laravel, fila de jobs e o Vite simultaneamente:
```bash
composer dev
```
Acesse o sistema no navegador: `http://localhost:8000/admin`

---

## 🔑 Configuração do Certificado Digital A1

Para configurar a emissão de notas fiscais:
1. Acesse o painel em **Sistema > Config. Nota Fiscal**.
2. Faça o upload do arquivo `.pfx` ou `.p12` do seu Certificado A1.
3. Informe a senha e clique no botão **Testar Certificado Digital**.

> 📖 **Documentação Detalhada do Certificado A1:**  
> Para orientações sobre suporte a ciphers legados, auto-detecção do OpenSSL ou implantação em servidores **Dokploy (Railpack v0.15.4)**, consulte:  
> 👉 [docs/CERTIFICADO_DIGITAL_A1.md](docs/CERTIFICADO_DIGITAL_A1.md)

---

## 🐳 Implantação em Produção (Dokploy / Railpack v0.15.4)

No ambiente de produção via **Dokploy** utilizando **Railpack (v0.15.4)**, defina as variáveis de ambiente no painel do Dokploy:

1. **Extensões PHP obrigatórias:**
   ```ini
   RAILPACK_PHP_EXTENSIONS="openssl, pdo_pgsql, pgsql, gd, zip, mbstring, xml, curl, bcmath"
   ```

2. **Pacotes de sistema Debian/Ubuntu (para o OpenSSL CLI):**
   ```ini
   RAILPACK_APT_PACKAGES="openssl"
   ```

Alternativamente via `nixpacks.toml`:
```toml
[providers]
providers = ["php", "node"]

[pkgs]
apt = ["openssl"]
```

---

## 🧪 Execução de Testes Automatizados

Para rodar a suíte completa de testes da aplicação:

```bash
composer test
# ou
php artisan test
```

Para rodar especificamente os testes do leitor de certificado A1:
```bash
php artisan test --filter=LeitorCertificadoTest
```

---

## 📄 Licença

Este projeto é de propriedade privada. Todos os direitos reservados.
