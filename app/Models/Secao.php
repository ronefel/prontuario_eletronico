<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Secao extends Model
{
    use HasFactory;

    protected $table = 'secoes';

    public function campos()
    {
        return $this->hasMany(Campo::class);
    }
}
