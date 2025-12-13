<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KitItem extends Model
{
    use HasFactory;

    protected $table = 'kit_produto';

    protected $fillable = [
        'kit_id',
        'produto_id',
        'quantidade',
    ];

    public function kit()
    {
        return $this->belongsTo(Kit::class);
    }

    public function produto()
    {
        return $this->belongsTo(Produto::class);
    }
}
