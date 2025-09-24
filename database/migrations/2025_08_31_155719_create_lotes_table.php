<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produto_id')->constrained()->onDelete('cascade');
            $table->foreignId('fornecedor_id')->nullable()->constrained('fornecedores')->onDelete('set null');
            $table->string('numero_lote')->unique();
            $table->date('data_fabricacao')->nullable();
            $table->date('data_validade')->nullable();
            $table->integer('quantidade_inicial');
            $table->string('documento')->nullable();
            $table->decimal('valor_unitario', 8, 2)->nullable();
            $table->foreignId('local_id')->constrained('locais')->onDelete('restrict');
            $table->enum('status', ['ativo', 'expirado', 'bloqueado'])->default('ativo');
            $table->timestamps();
            $table->softDeletes();

            $table->index('produto_id');
            $table->index('data_validade');
            $table->index('status');
            $table->index('deleted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lotes');
    }
};
