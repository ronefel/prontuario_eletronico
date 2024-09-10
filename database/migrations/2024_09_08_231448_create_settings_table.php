<?php

use App\Models\Setting;
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
        Schema::create('settings', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->string('label');
            $table->text('value')->nullable();
            $table->json('attributes')->nullable();
            $table->string('type');

            $table->timestamps();
        });

        Setting::create([
            'key' => 'cabecalho',
            'label' => 'Cabeçalho',
            'value' => null,
            'type' => 'text-editor',
        ]);

        Setting::create([
            'key' => 'rodape',
            'label' => 'Rodapé',
            'value' => null,
            'type' => 'text-editor',
        ]);

        Setting::create([
            'key' => 'margem_superior',
            'label' => 'Margem superior',
            'value' => 5,
            'type' => 'number',
        ]);

        Setting::create([
            'key' => 'margem_inferior',
            'label' => 'Margem inferior',
            'value' => 5,
            'type' => 'number',
        ]);

        Setting::create([
            'key' => 'margem_esquerda',
            'label' => 'Margem esquerda',
            'value' => 5,
            'type' => 'number',
        ]);

        Setting::create([
            'key' => 'margem_direita',
            'label' => 'Margem direita',
            'value' => 5,
            'type' => 'number',
        ]);

        Setting::create([
            'key' => 'altura_cabecalho',
            'label' => 'Altura do cabeçalho',
            'value' => 27,
            'type' => 'number',
        ]);

        Setting::create([
            'key' => 'altura_rodape',
            'label' => 'Altura do rodapé',
            'value' => 7,
            'type' => 'number',
        ]);

        //====================== EXEMPLO DE CAMPO SELECT ========================
        // Setting::create([
        //     'key' => 'environment',
        //     'label' => 'Environment',
        //     'value' => 'production',
        //     'type' => 'select',
        //     'attributes' => [
        //         'options' => [
        //             'production' => 'Production',
        //             'staging' => 'Staging',
        //             'local' => 'Local',
        //         ],
        //     ],
        // ]);
        //=======================================================================
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
