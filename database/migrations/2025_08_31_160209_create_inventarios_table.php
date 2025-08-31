<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventarios', function (Blueprint $table) {
            $table->id();
            $table->date('data_inventario');
            $table->enum('tipo', ['completo', 'ciclico', 'por_produto']);
            $table->foreignId('produto_id')->nullable()->constrained('produtos')->onDelete('cascade');
            $table->foreignId('lote_id')->nullable()->constrained('lotes')->onDelete('cascade');
            $table->integer('quantidade_contada');
            $table->integer('quantidade_registrada');
            $table->integer('discrepancia')->storedAs('quantidade_contada - quantidade_registrada');
            $table->text('motivo_discrepancia')->nullable();
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict');
            $table->enum('status', ['pendente', 'aprovado'])->default('pendente');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventarios');
    }
};
