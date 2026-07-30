<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('configuracoes_nota_fiscal', function (Blueprint $table) {
            $table->text('discriminacao_servico')->nullable()->after('aliquota_iss');
        });
    }

    public function down(): void
    {
        Schema::table('configuracoes_nota_fiscal', function (Blueprint $table) {
            $table->dropColumn('discriminacao_servico');
        });
    }
};
