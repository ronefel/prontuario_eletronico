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
        Schema::create('exame_testador', function (Blueprint $table) {
            $table->foreignId('exame_id')->constrained('exames')->onDelete('cascade');
            $table->foreignId('testador_id')->constrained('testadores')->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exame_testador');
    }
};
