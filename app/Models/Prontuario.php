<?php

namespace App\Models;

use App\Casts\DatetimeWithTimezone;
use App\Enums\ProntuarioTipoEnum;
use Illuminate\Support\Facades\Storage;

/**
 * @property int $id
 * @property int|null $paciente_id
 * @property string $descricao
 * @property mixed $data
 * @property ProntuarioTipoEnum|null $tipo
 * @property array<array-key, mixed>|null $arquivos
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Paciente|null $paciente
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Prontuario newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Prontuario newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Prontuario query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Prontuario whereArquivos($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Prontuario whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Prontuario whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Prontuario whereDescricao($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Prontuario whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Prontuario wherePacienteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Prontuario whereTipo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Prontuario whereUpdatedAt($value)
 * @mixin \Eloquent
 */
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
                'url' => '/files/'.$arquivo,
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
