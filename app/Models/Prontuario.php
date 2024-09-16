<?php

namespace App\Models;

use App\Casts\DatetimeWithTimezone;
use App\Enums\ProntuarioTipoEnum;
use Illuminate\Support\Facades\Storage;

class Prontuario extends BaseModel
{
    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }

    protected $casts = [
        'data' => DatetimeWithTimezone::class,
        'tipo' => ProntuarioTipoEnum::class,
        'arquivos' => 'array',
    ];

    // Retorna todos os arquivos do prontuário em um array com nome e url
    public function getArquivosComUrl()
    {
        $arquivos = [];
        foreach ($this->arquivos as $arquivo) {
            $arquivos[] = [
                'nome' => $arquivo,
                'url' => '/files/' . $arquivo,
            ];
        }
        return $arquivos;
    }

    protected static function booted()
    {
        static::updated(function ($prontuario) {
            // Lógica para gerenciar arquivos quando o modelo é atualizado
            $newFiles = $prontuario->arquivos ?? [];
            $oldFiles = $prontuario->getOriginal('arquivos') ?? [];

            // Determine quais arquivos foram removidos
            $deletedFiles = array_diff($oldFiles, $newFiles);

            // Exclua os arquivos removidos do disco
            foreach ($deletedFiles as $file) {
                if (Storage::disk('database')->exists($file)) {
                    Storage::disk('database')->delete($file);
                }
            }
        });

        static::deleted(function ($prontuario) {
            // Lógica para lidar com a exclusão do modelo
            foreach ($prontuario->arquivos ?? [] as $file) {
                if (Storage::disk('database')->exists($file)) {
                    Storage::disk('database')->delete($file);
                }
            }
        });
    }
}
