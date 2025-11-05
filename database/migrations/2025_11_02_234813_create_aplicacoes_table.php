<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aplicacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tratamento_id')->constrained('tratamentos')->onDelete('cascade');
            $table->foreignId('lote_id')->constrained('lotes')->onDelete('restrict');
            $table->integer('quantidade'); // Ex: 2 ampolas
            $table->dateTime('data_aplicacao');
            $table->text('observacoes')->nullable();
            $table->enum('status', ['agendada', 'aplicada', 'cancelada'])->default('agendada');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aplicacoes');
    }
};
