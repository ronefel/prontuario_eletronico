<?php

use App\Models\Cidade;
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
        Schema::create('cidades', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('uf');
            $table->timestamps();

            $table->unique(['nome', 'uf']);
        });

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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cidades');
    }
};
