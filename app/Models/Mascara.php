<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Mascara extends BaseModel
{
    use HasFactory;

    protected $fillable = ['nome', 'descricao'];
}
