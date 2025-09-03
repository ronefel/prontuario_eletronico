<?php

namespace App\Http\Controllers;

use App\Models\Mascara;

class MascaraController extends Controller
{
    public function index()
    {
        return response()->json(Mascara::orderBy('nome')->get(['nome', 'descricao']));
    }
}
