<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class Local extends BaseModel
{
    use SoftDeletes;

    protected $table = 'locais';

    protected $fillable = ['nome', 'endereco', 'capacidade'];

    public function lotes()
    {
        return $this->hasMany(Lote::class);
    }
}
