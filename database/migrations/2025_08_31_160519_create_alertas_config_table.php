<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alertas_config', function (Blueprint $table) {
            $table->id();
            $table->enum('tipo', ['estoque_baixo', 'validade_proxima', 'excesso', 'discrepancia']);
            $table->foreignId('produto_id')->nullable()->constrained('produtos')->onDelete('cascade');
            $table->integer('threshold'); // Ex: qtd mínima ou dias para validade
            $table->enum('metodo_notificacao', ['email', 'in_app', 'sms'])->default('in_app');
            $table->json('usuarios_notificados')->nullable(); // Array de IDs
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alertas_config');
    }
};
