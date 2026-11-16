<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RfidStreamController;
use App\Http\Controllers\AreaController;

Route::get('/', function () {
    return view('welcome');
});

require __DIR__ . '/auth.php';

// Home / Dashboard
Route::get('/home', [RfidStreamController::class, 'index'])->name('home')->middleware('auth');
Route::get('/rfid-stream/data', [RfidStreamController::class, 'fetchRows']); // AJAX route

// Area Management Routes
Route::middleware('auth')->group(function () {
    Route::resource('areas', AreaController::class);
    // Legacy URL alias matching the screenshot reference
    Route::get('/Vehicle_controller/view_area', [AreaController::class, 'index']);
});
