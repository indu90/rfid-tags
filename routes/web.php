<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RfidStreamController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\AreaController;

Route::get('/', function () {
    return view('welcome');
});

require __DIR__ . '/auth.php';

// Home / Dashboard
Route::get('/home', [RfidStreamController::class, 'index'])->name('home')->middleware('auth');
Route::get('/rfid-stream/data', [RfidStreamController::class, 'fetchRows']); // New AJAX route
Route::middleware(['auth'])->group(function () {
    Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserManagementController::class, 'create'])->name('users.create');
    Route::post('/users', [UserManagementController::class, 'store'])->name('users.store');
    Route::get('/roles-permissions', [UserManagementController::class, 'rolesPermissions'])->name('roles.index');
    Route::post('/roles-permissions', [UserManagementController::class, 'storeRole'])->name('roles.store');
    Route::get('/users/{user}/edit', [UserManagementController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [UserManagementController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserManagementController::class, 'destroy'])->name('users.destroy');
    Route::get('/roles-permissions/{role}/edit', [UserManagementController::class, 'editRole'])->name('roles.edit');
    Route::put('/roles-permissions/{role}', [UserManagementController::class, 'updateRole'])->name('roles.update');
    Route::delete('/roles-permissions/{role}', [UserManagementController::class, 'destroyRole'])->name('roles.destroy');
    Route::post('/permissions', [UserManagementController::class, 'storePermission'])->name('permissions.store');
    // Area Management Routes
    Route::get('/areas', [AreaController::class, 'index'])->name('areas.index');
    Route::get('/areas/create', [AreaController::class, 'create'])->name('areas.create');
    Route::post('/areas', [AreaController::class, 'store'])->name('areas.store');
    Route::get('/areas/{area}/edit', [AreaController::class, 'edit'])->name('areas.edit');
    Route::put('/areas/{area}', [AreaController::class, 'update'])->name('areas.update');
    Route::delete('/areas/{area}', [AreaController::class, 'destroy'])->name('areas.destroy');
});
