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
            $table->enum('tipo', ['completo', 'por_local', 'por_produto']);
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
