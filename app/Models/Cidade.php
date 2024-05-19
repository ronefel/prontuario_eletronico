<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class Cidade extends Model
{
    use HasFactory;

    public static function rules($id = null)
    {
        return [
            'nome' => 'required',
            'uf' => [
                'required',
                Rule::unique('cidades', 'uf')->ignore($id)->where(function ($query) {
                    return $query->where('nome', request()->post('nome'));
                }),
            ],
        ];
    }
}
