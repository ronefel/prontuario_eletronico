<?php

namespace Database\Seeders;

use App\Models\Cidade;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CidadesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cidades = [
            'Alta Floresta D\'Oeste',
            'Alto Alegre dos Parecis',
            'Alto Paraíso',
            'Alvorada D\'Oeste',
            'Ariquemes',
            'Buritis',
            'Cabixi',
            'Cacaulândia',
            'Cacoal',
            'Campo Novo de Rondônia',
            'Candeias do Jamari',
            'Castanheiras',
            'Cerejeiras',
            'Chupinguaia',
            'Colorado do Oeste',
            'Corumbiara',
            'Costa Marques',
            'Cujubim',
            'Espigão D\'Oeste',
            'Governador Jorge Teixeira',
            'Guajará-Mirim',
            'Itapuã do Oeste',
            'Jaru',
            'Ji-Paraná',
            'Machadinho D\'Oeste',
            'Ministro Andreazza',
            'Mirante da Serra',
            'Monte Negro',
            'Nova Brasilândia D\'Oeste',
            'Nova Mamoré',
            'Nova União',
            'Novo Horizonte do Oeste',
            'Ouro Preto do Oeste',
            'Parecis',
            'Pimenta Bueno',
            'Pimenteiras do Oeste',
            'Porto Velho',
            'Presidente Médici',
            'Primavera de Rondônia',
            'Rio Crespo',
            'Rolim de Moura',
            'Santa Luzia D\'Oeste',
            'São Felipe D\'Oeste',
            'São Francisco do Guaporé',
            'São Miguel do Guaporé',
            'Seringueiras',
            'Teixeirópolis',
            'Theobroma',
            'Urupá',
            'Vale do Anari',
            'Vale do Paraíso',
            'Vilhena',
        ];

        foreach ($cidades as $cidade) {
            Cidade::firstOrCreate(
                ['nome' => $cidade],
                ['uf' => 'RO']
            );
        }
    }
}
