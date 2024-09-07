<?php

use App\Http\Controllers\ProntuarioController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // return view('welcome');
    return redirect('/admin');
});

Route::get('/prontuario/print/{id}', [ProntuarioController::class, 'print'])
    ->middleware('auth')
    ->name('prontuario.print');
