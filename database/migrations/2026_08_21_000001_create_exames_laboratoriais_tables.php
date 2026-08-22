<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('exames_laboratoriais', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->text('descricao')->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });

        Schema::create('exame_parametros', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exame_id')->constrained('exames_laboratoriais')->cascadeOnDelete();
            $table->string('nome_parametro');
            $table->string('unidade_medida')->nullable();
            $table->decimal('valor_minimo_ideal', 10, 2)->nullable();
            $table->decimal('valor_maximo_ideal', 10, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('exame_registros', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paciente_id')->constrained('pacientes')->cascadeOnDelete();
            $table->foreignId('exame_id')->constrained('exames_laboratoriais')->cascadeOnDelete();
            $table->date('data_exame');
            $table->text('observacoes')->nullable();
            $table->timestamps();
        });

        Schema::create('exame_resultado_itens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exame_registro_id')->constrained('exame_registros')->cascadeOnDelete();
            $table->foreignId('exame_parametro_id')->constrained('exame_parametros')->cascadeOnDelete();
            $table->decimal('valor_resultado', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exame_resultado_itens');
        Schema::dropIfExists('exame_registros');
        Schema::dropIfExists('exame_parametros');
        Schema::dropIfExists('exames_laboratoriais');
    }
};
