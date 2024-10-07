<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InfoController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/info/server', [InfoController::class, 'serverInfo']);
Route::get('/info/client', [InfoController::class, 'clientInfo']);
Route::get('/info/database', [InfoController::class, 'databaseInfo']);
