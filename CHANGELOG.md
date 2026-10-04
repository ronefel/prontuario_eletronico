# Changelog

Todas as mudanças notáveis neste projeto serão documentadas neste arquivo.

O formato é baseado em [Keep a Changelog](https://keepachangelog.com/pt-BR/1.0.0/),
e este projeto adere ao [Versionamento Semântico](https://semver.org/lang/pt-BR/).

## [1.7.0] - 2026-10-04

### Adicionado
- Modos de seleção de testadores na Biorressonância (lista de checkboxes ou seleção com busca).
- Exibição do ano do exame nos cabeçalhos da tabela de Biorressonância.
- Configuração de ambiente Docker via Laravel Sail com PostgreSQL e ajustes de PHP.

### Modificado
- Paginação nos widgets de estoque baixo e lotes a vencer no Dashboard.
- Melhoria no layout do formulário de exames laboratoriais e extração de acessor para intervalo ideal.
- Aprimoramento na lógica de seleção de lotes e tratamento de fallback em `AplicacoesRelationManager`.
- Remoção do limite fixo de pacientes no widget `UltimosPacientes`.
- Ação simplificada para impressão do laudo evolutivo de exames.

## [1.6.0] - 2026-09-15

### Adicionado
- Gestão completa de exames laboratoriais do paciente com histórico, registro de resultados e widgets de gráficos de evolução (`ExameEvolucaoChartWidget`).
- Geração e exportação em PDF de laudo evolutivo laboratorial.
- Sugestão com datalist de unidades de medida no formulário de exames.
- Resumo visual de resultados de exames por paciente.

### Modificado
- Ajustes de estilo e margens dinâmicas no prontuário.
- Correções de análise estática (PHPStan).

## [1.5.0] - 2026-08-03

### Adicionado
- Módulo de emissão de Nota Fiscal de Serviço Eletrônica (NFS-e) no padrão ABRASF v2.02.
- Serviço de leitura e manipulação de certificados digitais A1 (suporte a OpenSSL legado).
- Assinatura digital e geração de XML de lote e cancelamento para envio via SOAP.
- Fluxos de consulta, cancelamento e substituição de NFS-e.
- Serviço e visualização para impressão/espelho da NFS-e.
- Integração com ViaCEP com cache local e sincronização automática do código IBGE do município.
- Validação de CPF nos dados do paciente e obrigatoriedade do CEP.
- Componente customizado `KeyValue` com controle de largura de colunas para atividades e CNAE.
- Limpeza automática do campo de pesquisa no componente Select ao selecionar via teclado ou mouse.

### Modificado
- Padronização de estilo de código (PSR-12 / Laravel Pint).
- Permissão para edição e exclusão de notas fiscais rejeitadas.

## [1.4.1] - 2026-07-11

### Adicionado
- Suporte à execução de comandos PHP dentro do WSL no script de automação de releases (`release.ps1`).

### Modificado
- Agenda definida como página inicial padrão do sistema.
- Aprimoramentos e correções na experiência offline do PWA.

## [1.4.0] - 2026-06-18

### Adicionado
- Suporte a Progressive Web App (PWA):
    - Configuração de Service Worker e Web Manifest (`manifest.json`).
    - Ícones da aplicação para instalação em dispositivos.
    - Página de fallback offline.
- Aprimoramentos na visualização da agenda de consultas com navegação por calendário e gestão de horários.

## [1.3.0] - 2026-06-17

### Adicionado
- Módulo completo de Agenda e Gestão de Consultas:
    - Interface interativa com calendário mensal e grade de horários diários.
    - Suporte a agendamentos com cálculo de fuso horário.
- Integração bidirecional com Google Calendar API:
    - Sincronização automática de consultas.
    - Rotas de autorização e autenticação OAuth.
    - Observers de modelo para sincronização em tempo real.
- Rastreamento e movimentações automáticas no modelo de lotes de estoque.

### Corrigido
- Ajuste no caminho do arquivo de estilos CSS para geração de relatórios em PDF.

## [1.2.2] - 2026-06-05

### Adicionado
- Substituição do TinyMCE pelo CKEditor customizado nos campos de texto rico.
- Relatórios de contagem e conferência de inventário.

### Modificado
- Atualização da base do framework para Laravel 13 e Filament v5.
- Adaptação do armazenamento e upload de arquivos com Livewire.
- Configuração de confiança para proxies reversos no middleware (`trustProxies`).
- Melhoria na exibição das observações cadastrais do paciente.

## [1.2.1] - 2026-03-01

### Adicionado
- Nova página de Consultório:
    - Visualização detalhada do paciente com abas para Registros e Tratamentos.
    - Integração completa para criação e edição de tratamentos diretamente na página.
- Melhorias na UI:
    - Cabeçalho do paciente aprimorado com informações de contato, idade e tipo sanguíneo.
    - Uso de componentes Filament Infolist para exibição organizada de dados.

## [1.2.0] - 2026-01-02

### Adicionado
- Melhorias na usabilidade dos formulários de estoque:
    - Selects de produtos e fornecedores agora são pesquisáveis e permitem criação rápida.
    - Seleção automática de "Local" se houver apenas um registro cadastrado.
- Refinamento do Relatório de Tratamentos:
    - Exibição de aplicações e itens em formato de lista no PDF, Excel e UI.
    - Limite de caracteres nos nomes de produtos para melhor visualização.
    - Ajuste automático de orientação para paisagem no PDF ao incluir aplicações.
- Otimização do Dashboard:
    - Reordenação e ajuste de layout dos widgets.
    - Widget de validade ampliado e com informações mais detalhadas.
- Correção de diversos erros de análise estática (PHPStan).

## [1.1.0] - 2025-12-21

### Adicionado
- Recursos do Filament para gerenciamento de estoque:
    - `CategoriaResource`
    - `FornecedorResource`
    - `InventarioResource`
    - `LoteResource`
    - `MovimentacaoResource`
    - `ProdutoResource`

## [1.0.0] - 2025-12-20

### Adicionado
- Estratégia de versionamento inicial.
- Configuração de versão no `config/app.php`.
- Script de automação para releases (`release.ps1`).
- Este arquivo `CHANGELOG.md`.
