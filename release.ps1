# Release Automation Script
# Este script automatiza o processo de verificacao antes de um fechamento de versao.

Write-Host "Iniciando processo de verificacao de release..." -ForegroundColor Cyan

# 1. Executar Testes
Write-Host "Executando testes (Pest)..." -ForegroundColor Yellow
php artisan test
if ($LASTEXITCODE -ne 0) {
    Write-Host "ERRO: Os testes falharam. Corrija-os antes de fechar a versao." -ForegroundColor Red
    exit $LASTEXITCODE
}

# 2. Executar Analise Estatica
Write-Host "Executando analise estatica (PHPStan)..." -ForegroundColor Yellow
./vendor/bin/phpstan analyze --memory-limit=2G
if ($LASTEXITCODE -ne 0) {
    Write-Host "ERRO: A analise estatica falhou. Corrija os erros antes de fechar a versao." -ForegroundColor Red
    exit $LASTEXITCODE
}

# 3. Verificar Estilo de Codigo
Write-Host "Verificando estilo de codigo (Pint)..." -ForegroundColor Yellow
./vendor/bin/pint --test
if ($LASTEXITCODE -ne 0) {
    Write-Host "AVISO: O estilo de codigo nao esta padronizado. Execute './vendor/bin/pint' para corrigir." -ForegroundColor Yellow
}

Write-Host "Sucesso! O projeto esta pronto para o fechamento da versao." -ForegroundColor Green
Write-Host ""
Write-Host "Passos para finalizar a release:" -ForegroundColor Cyan
Write-Host "1. Revise o arquivo CHANGELOG.md"
Write-Host "2. Confirme a versao no config/app.php"
Write-Host "3. git add ."
Write-Host "4. git commit -m 'chore: release v1.x.x' (O Git Hook validara tudo novamente)"
Write-Host "5. git tag v1.x.x"
Write-Host "6. git push origin master --tags"
Write-Host ""
