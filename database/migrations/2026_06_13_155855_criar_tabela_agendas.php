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
        Schema::create('agendas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paciente_id')
                ->nullable()
                ->constrained('pacientes')
                ->nullOnDelete();
            $table->string('nome_paciente')->nullable();
            $table->string('whatsapp_paciente')->nullable();
            $table->dateTime('data_inicio');
            $table->dateTime('data_fim');
            $table->string('status')->default('agendado');
            $table->text('observacoes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agendas');
    }
};
