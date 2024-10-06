<?php

use App\Http\Controllers\BiorressonanciaController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\MascaraController;
use App\Http\Controllers\ProntuarioController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin');
});

Route::get('/login', function () {
    return redirect()->route('filament.admin.auth.login');
})->name('login');

Route::get('/prontuario/print/{id}', [ProntuarioController::class, 'print'])
    ->middleware('auth')
    ->name('prontuario.print');

Route::get('/biorressonancia/print/{id}', [BiorressonanciaController::class, 'print'])
    ->middleware('auth')
    ->name('biorressonancia.print');

Route::get('/list-mascaras', [MascaraController::class, 'index'])
    ->middleware('auth')
    ->name('mascaras.index');

Route::get('/files/{name}', [FileController::class, 'serve'])
    ->middleware('auth')
    ->name('files.serve');
