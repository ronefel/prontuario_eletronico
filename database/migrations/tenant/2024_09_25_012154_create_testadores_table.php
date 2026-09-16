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
        Schema::create('testadores', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 10);
            $table->string('nome');
            $table->foreignId('categoria_testador_id')->constrained('categorias_testadores')->restrictOnDelete();
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('testadores');
    }
};
