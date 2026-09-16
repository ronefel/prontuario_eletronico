<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class TenantDatabaseSeeder extends Seeder
{
    /**
     * Alimenta o banco de dados do tenant recém-criado.
     */
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,
            CidadesSeeder::class,
            SettingsSeeder::class,
            ExameLaboratorialSeeder::class,
        ]);
    }
}
