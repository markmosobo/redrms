<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\TenancyController;
use App\Http\Controllers\DepositController;
use App\Http\Controllers\InspectionController;
use App\Http\Controllers\DeductionController;
use App\Http\Controllers\RefundController;
use App\Http\Controllers\AuditLogController;

Route::middleware('auth:sanctum')->group(function () {

    // Current logged-in user info
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Users
    Route::apiResource('users', UserController::class);

    // Properties
    Route::apiResource('properties', PropertyController::class);

    // Units
    Route::apiResource('units', UnitController::class);

    // Tenancies
    Route::apiResource('tenancies', TenancyController::class);

    // Deposits
    Route::apiResource('deposits', DepositController::class);

    // Inspections
    Route::apiResource('inspections', InspectionController::class);

    // Deductions
    Route::apiResource('deductions', DeductionController::class);

    // Refunds
    Route::apiResource('refunds', RefundController::class);

    // Audit Logs
    Route::apiResource('audit-logs', AuditLogController::class);
});