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
        Schema::create('agenda_configuracoes', function (Blueprint $table) {
            $table->id();
            $table->string('client_id')->nullable();
            $table->string('client_secret')->nullable();
            $table->string('redirect_uri')->nullable();
            $table->string('calendario_id')->nullable()->default('primary');
            $table->text('token_acesso')->nullable();
            $table->text('token_atualizacao')->nullable();
            $table->dateTime('token_expira_em')->nullable();
            
            // Novas configurações de atendimento
            $table->string('hora_inicio')->default('08:00');
            $table->string('hora_fim')->default('18:00');
            $table->integer('intervalo')->default(30); // em minutos

            // Intervalo de pausa (ex: almoço)
            $table->string('pausa_inicio')->nullable();
            $table->string('pausa_fim')->nullable();

            // Controle de disponibilidade por dia
            $table->string('modo_limite')->default('slots'); // 'slots' ou 'manual'
            $table->integer('limite_consultas_dia')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agenda_configuracoes');
    }
};
