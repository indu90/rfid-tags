<?php

use App\Http\Controllers\Api\RfidTagController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('dashboard'); // Loads resources/views/dashboard.blade.php
});
// Public or API-Key protected RFID capture endpoint
Route::post('/tags', [RfidTagController::class, 'store']);
