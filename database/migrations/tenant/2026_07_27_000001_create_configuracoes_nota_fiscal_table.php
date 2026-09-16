<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuracoes_nota_fiscal', function (Blueprint $table) {
            $table->id();

            // Dados da Empresa / Emitente
            $table->string('cnpj', 20);
            $table->string('inscricao_municipal', 20)->nullable();
            $table->string('razao_social');
            $table->string('nome_fantasia')->nullable();

            // Endereço do Emitente
            $table->string('cep', 10)->nullable();
            $table->string('logradouro')->nullable();
            $table->string('numero', 10)->nullable();
            $table->string('complemento')->nullable();
            $table->string('bairro')->nullable();
            $table->string('codigo_municipio_ibge', 7)->default('1100049'); // Cacoal - RO
            $table->string('uf', 2)->default('RO');

            // Configurações Fiscais (ABRASF)
            $table->unsignedTinyInteger('regime_especial_tributacao')->default(0); // 0-Nenhum, 1-Microempresa, etc.
            $table->boolean('optante_simples_nacional')->default(true);
            $table->boolean('incentivador_cultural')->default(false);
            $table->string('item_lista_servico', 10)->default('04.01'); // Serviços de saúde / medicina
            $table->string('codigo_tributacao_municipio', 20)->nullable();
            $table->decimal('aliquota_iss', 5, 2)->default(2.00); // Ex: 2.00%
            $table->text('discriminacao_servico')->nullable();
            $table->json('atividades')->nullable(); // Lista de atividades municipais cadastradas
            $table->json('cnaes')->nullable(); // Lista de CNAEs cadastrados

            // Certificado Digital
            $table->string('caminho_certificado')->nullable(); // Caminho do arquivo .pfx / .p12
            $table->text('senha_certificado')->nullable(); // Criptografado

            // Controle de Numeração de RPS e Ambiente
            $table->string('serie_rps', 5)->default('1');
            $table->unsignedBigInteger('ultimo_numero_rps')->default(0);
            $table->string('ambiente', 15)->default('homologacao'); // homologacao / producao
            $table->string('url_webservice_homologacao')->default('https://homologacao.webiss.com.br/ws/nfse.asmx');
            $table->string('url_webservice_producao')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('configuracoes_nota_fiscal');
    }
};
