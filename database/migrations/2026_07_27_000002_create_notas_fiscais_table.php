<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('notas_fiscais', function (Blueprint $table) {
            $table->id();

            // Relacionamentos
            $table->foreignId('paciente_id')->constrained('pacientes')->cascadeOnDelete();
            $table->foreignId('tratamento_id')->nullable()->constrained('tratamentos')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            // Identificação do RPS
            $table->unsignedBigInteger('numero_rps');
            $table->string('serie_rps', 5)->default('1');
            $table->unsignedTinyInteger('tipo_rps')->default(1); // 1-RPS
            $table->dateTime('data_emissao_rps');

            // Retorno da NFS-e Autorizada
            $table->string('numero_nfse', 30)->nullable();
            $table->string('codigo_verificacao', 50)->nullable();
            $table->dateTime('data_emissao_nfse')->nullable();

            // Valores Financeiros e Impostos
            $table->decimal('valor_servicos', 12, 2);
            $table->decimal('valor_deducoes', 12, 2)->default(0.00);
            $table->decimal('valor_pis', 12, 2)->default(0.00);
            $table->decimal('valor_cofins', 12, 2)->default(0.00);
            $table->decimal('valor_inss', 12, 2)->default(0.00);
            $table->decimal('valor_ir', 12, 2)->default(0.00);
            $table->decimal('valor_csll', 12, 2)->default(0.00);
            $table->decimal('valor_iss', 12, 2)->default(0.00);
            $table->decimal('aliquota_iss', 5, 2)->default(2.00);
            $table->decimal('outras_retencoes', 12, 2)->default(0.00);
            $table->decimal('desconto_incondicionado', 12, 2)->default(0.00);
            $table->decimal('desconto_condicionado', 12, 2)->default(0.00);

            // Dados do Serviço
            $table->string('item_lista_servico', 10)->default('04.01');
            $table->string('codigo_cnae', 20)->nullable();
            $table->string('codigo_tributacao_municipio', 20)->nullable();
            $table->text('discriminacao_servico');
            $table->string('codigo_municipio_ibge', 7)->default('1100049');

            // Status da Emissão
            $table->string('status', 20)->default('rascunho'); // rascunho, processando, autorizada, cancelada, rejeitada
            $table->string('protocolo_lote', 50)->nullable();

            // Conteúdo XML e Mensagens de Erro
            $table->longText('xml_rps')->nullable();
            $table->longText('xml_envio')->nullable();
            $table->longText('xml_retorno')->nullable();
            $table->string('codigo_erro', 50)->nullable();
            $table->text('mensagem_erro')->nullable();

            // Cancelamento e Substituição
            $table->string('codigo_cancelamento', 5)->nullable();
            $table->text('motivo_cancelamento')->nullable();
            $table->dateTime('data_cancelamento')->nullable();
            $table->foreignId('nota_fiscal_substituida_id')->nullable()->constrained('notas_fiscais')->nullOnDelete();
            $table->foreignId('nota_fiscal_substituta_id')->nullable()->constrained('notas_fiscais')->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notas_fiscais');
    }
};
