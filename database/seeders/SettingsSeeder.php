<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Setting::firstOrCreate([
            'key' => 'cabecalho',
            'label' => 'Cabeçalho',
            'value' => null,
            'type' => 'text-editor',
        ]);

        Setting::firstOrCreate([
            'key' => 'rodape',
            'label' => 'Rodapé',
            'value' => null,
            'type' => 'text-editor',
        ]);

        Setting::firstOrCreate([
            'key' => 'margem_superior',
            'label' => 'Margem superior',
            'value' => 5,
            'type' => 'number',
        ]);

        Setting::firstOrCreate([
            'key' => 'margem_inferior',
            'label' => 'Margem inferior',
            'value' => 5,
            'type' => 'number',
        ]);

        Setting::firstOrCreate([
            'key' => 'margem_esquerda',
            'label' => 'Margem esquerda',
            'value' => 5,
            'type' => 'number',
        ]);

        Setting::firstOrCreate([
            'key' => 'margem_direita',
            'label' => 'Margem direita',
            'value' => 5,
            'type' => 'number',
        ]);

        Setting::firstOrCreate([
            'key' => 'altura_cabecalho',
            'label' => 'Altura do cabeçalho',
            'value' => 27,
            'type' => 'number',
        ]);

        Setting::firstOrCreate([
            'key' => 'altura_rodape',
            'label' => 'Altura do rodapé',
            'value' => 7,
            'type' => 'number',
        ]);

        Setting::firstOrCreate([
            'key' => 'biorressonancia_texto_inicial',
            'label' => 'Biorressonância - Texto Inicial',
            'value' => null,
            'type' => 'text-editor',
        ]);

        Setting::firstOrCreate([
            'key' => 'biorressonancia_texto_final',
            'label' => 'Biorressonância - Texto Final',
            'value' => null,
            'type' => 'text-editor',
        ]);

        //====================== EXEMPLO DE CAMPO SELECT ========================
        // Setting::firstOrCreate([
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
}
