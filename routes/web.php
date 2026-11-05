<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RfidStreamController;

Route::get('/', function () {
    return view('welcome');
});
require __DIR__ . '/auth.php';

//Route::get('/home', [\App\Http\Controllers\HomeController::class, 'index'])->name('home')->middleware('auth');
Route::get('/home', [RfidStreamController::class, 'index'])->name('home')->middleware('auth');
Route::get('/rfid-stream/data', [RfidStreamController::class, 'fetchRows']); // New AJAX route