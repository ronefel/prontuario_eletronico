<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Exame extends BaseModel
{
    use HasFactory;

    public function testadores()
    {
        return $this->belongsToMany(Testador::class, 'exame_testador');
    }
}
