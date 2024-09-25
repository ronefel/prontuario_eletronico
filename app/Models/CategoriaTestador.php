<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CategoriaTestador extends BaseModel
{
    use HasFactory;

    protected $table = 'categorias_testadores';

    public function testadores(): HasMany
    {
        return $this->hasMany(Testador::class);
    }
}
