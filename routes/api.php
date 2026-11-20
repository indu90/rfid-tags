<?php

use App\Http\Controllers\Api\RfidTagController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('dashboard'); // Loads resources/views/dashboard.blade.php
});
// Public or API-Key protected RFID capture endpoint
Route::match(['get', 'post'], '/rfid/logs', [RfidTagController::class, 'store']);
