<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movimentacoes', function (Blueprint $table) {
            $table->id();
            $table->enum('tipo', ['entrada', 'saida', 'ajuste', 'transferencia']);
            $table->foreignId('produto_id')->constrained('produtos')->onDelete('cascade');
            $table->index('produto_id');
            $table->foreignId('lote_id')->constrained('lotes')->onDelete('cascade');
            $table->index('lote_id');
            $table->integer('quantidade');
            $table->dateTime('data_movimentacao');
            $table->text('motivo')->nullable();
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict');
            $table->string('documento')->nullable();
            $table->decimal('valor_unitario', 8, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index('deleted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimentacoes');
    }
};
