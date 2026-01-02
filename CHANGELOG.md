# Changelog

Todas as mudanças notáveis neste projeto serão documentadas neste arquivo.

O formato é baseado em [Keep a Changelog](https://keepachangelog.com/pt-BR/1.0.0/),
e este projeto adere ao [Versionamento Semântico](https://semver.org/lang/pt-BR/).

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
