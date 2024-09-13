<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Secao extends BaseModel
{
    use HasFactory;

    protected $table = 'secoes';

    public function campos()
    {
        return $this->hasMany(Campo::class);
    }
}
