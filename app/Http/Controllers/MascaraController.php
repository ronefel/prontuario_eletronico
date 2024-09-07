<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Mascara;
use Illuminate\Http\Request;

class MascaraController extends Controller
{
    public function index()
    {
        return response()->json(Mascara::orderBy('nome')->get(['nome', 'descricao']));
    }
}
