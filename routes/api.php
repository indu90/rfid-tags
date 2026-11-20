<?php

use App\Http\Controllers\AssetController;
use App\Http\Controllers\Api\AssetRfidAssignmentController;
use App\Http\Controllers\Api\RfidAuditLogController;
use App\Http\Controllers\Api\RfidScanIngestionController;
use App\Http\Controllers\Api\RfidTagController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('dashboard'); // Loads resources/views/dashboard.blade.php
});
// Public or API-Key protected RFID capture endpoint
Route::match(['get', 'post'], '/rfid/logs', [RfidTagController::class, 'store']);
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('assets', AssetController::class);
    Route::get('assets/by-rfid/{epc}', [AssetController::class, 'showByEpc']);

    Route::apiResource('rfid-tags', RfidTagController::class)->only(['index', 'store', 'show']);
    Route::post('rfid-tags/batch', [RfidTagController::class, 'storeBatch']);

    Route::post('assets/{asset}/rfid-tags/assign', [AssetRfidAssignmentController::class, 'assign']);
    Route::post('rfid-tags/{rfidTag}/unassign', [AssetRfidAssignmentController::class, 'unassign']);

    Route::post('rfid-scans', [RfidScanIngestionController::class, 'store']);

    Route::get('rfid-audit-logs', [RfidAuditLogController::class, 'index']);
    Route::get('assets/{asset}/rfid-audit-logs', [RfidAuditLogController::class, 'byAsset']);
});
