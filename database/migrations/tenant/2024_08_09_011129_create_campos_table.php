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
        Schema::create('campos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('secao_id')->nullable();
            $table->foreign('secao_id')->references('id')->on('secoes');
            $table->string('titulo');
            $table->string('tipo');
            $table->string('unidade')->nullable();
            $table->smallInteger('casasdescimais')->nullable();
            $table->string('sim')->nullable();
            $table->string('nao')->nullable();
            $table->json('lista')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campos');
    }
};
