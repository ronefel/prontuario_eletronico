<?php

use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyBySubdomain;

/*
|--------------------------------------------------------------------------
| Web Routes (Central & Global Fallback)
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    if (function_exists('tenant') && tenant()) {
        return redirect('/admin');
    }

    return redirect('/central');
})->middleware([
    'web',
    'universal',
    InitializeTenancyBySubdomain::class,
]);
