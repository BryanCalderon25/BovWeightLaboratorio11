<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\FarmController;
use App\Http\Controllers\Api\AnimalController;
use App\Http\Controllers\Api\WeightRecordController;
use App\Http\Controllers\Api\MLIntegrationController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\FarmInvitationController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::post('/registro', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/invitaciones/resolver/{token}', [FarmInvitationController::class, 'resolveGuestAccess']);

Route::middleware('auth:sanctum')->group(function () {
    // Perfil y Sesión
    Route::get('/perfil', [AuthController::class, 'profile']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Fincas (Farms)
    Route::apiResource('fincas', FarmController::class);

    // Animales (Animals)
    Route::get('/fincas/{farmId}/animales', [AnimalController::class, 'index']);
    Route::post('/animales', [AnimalController::class, 'store']);
    Route::get('/animales/{animal}', [AnimalController::class, 'show']);
    Route::put('/animales/{animal}', [AnimalController::class, 'update']);
    Route::delete('/animales/{animal}', [AnimalController::class, 'destroy']);

    // Registros de Pesaje (Weight Records)
    Route::get('/pesajes', [WeightRecordController::class, 'listAll']);
    Route::get('/animales/{animalId}/pesajes', [WeightRecordController::class, 'index']);
    Route::post('/pesajes', [WeightRecordController::class, 'store']);

    // Integración con Machine Learning
    Route::post('/ml/estimar-peso', [MLIntegrationController::class, 'predictWeight']);

    // Reportes
    Route::get('/reportes/generar', [ReportController::class, 'generateReport']);
    Route::get('/animales/{animalId}/reporte', [ReportController::class, 'generateAnimalReport']);

    // Invitaciones
    Route::post('/invitaciones', [FarmInvitationController::class, 'store']);

    // Sincronización Offline
    Route::post('/sincronizacion/pesajes', [App\Http\Controllers\Api\OfflineSyncController::class, 'syncWeightRecords']);
});
