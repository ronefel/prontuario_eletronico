<?php

use App\Http\Controllers\FileController;
use App\Http\Controllers\MascaraController;
use App\Http\Controllers\ProntuarioController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // return view('welcome');
    return redirect('/admin');
});

Route::get('/prontuario/print/{id}', [ProntuarioController::class, 'print'])
    ->middleware('auth')
    ->name('prontuario.print');

Route::get('/mascaras', [MascaraController::class, 'index'])
    ->middleware('auth')
    ->name('mascaras.index');

Route::get('/files/{name}', [FileController::class, 'serve'])
    ->middleware('auth')
    ->name('files.serve');
