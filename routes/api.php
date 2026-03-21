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
use App\Http\Controllers\AuthController;

Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/login', [AuthController::class, 'login'])->name('login');

Route::middleware('auth:api')->group(function () {

    // Current logged-in user info
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::get('/landlords', [UserController::class, 'landlords']);
    Route::get('/landlords/{landlord}/properties', [PropertyController::class, 'landlordProperties']);
    Route::post('/landlords/{landlord}/properties', [PropertyController::class, 'storeProperty']);    
    Route::get('/managers', [UserController::class, 'managers']);
    Route::get('/tenants', [UserController::class, 'tenants']);

    // Users
    Route::apiResource('users', UserController::class);

    // Properties
    Route::apiResource('properties', PropertyController::class);
    Route::get('/properties/{property}/units', [UnitController::class, 'unitsByProperty']);
    Route::post('/properties/{property}/units', [UnitController::class, 'storeUnit']);

    // Units
    Route::get('/units/vacant', [UnitController::class, 'vacant']);
    Route::get('/units/{unit}/tenancy', [TenancyController::class, 'showTenancy']);
    Route::post('/units/{unit}/tenancy', [TenancyController::class, 'storeTenancy']);
    Route::apiResource('units', UnitController::class);

    // Tenancies
    Route::apiResource('tenancies', TenancyController::class);
    Route::put('/tenancies/{tenancy}', [TenancyController::class, 'updateTenancy']);
    Route::post('/tenancies/{tenancy}/terminate', [TenancyController::class, 'terminate']);

    // Deposits
    Route::apiResource('deposits', DepositController::class);

    // Inspections
    Route::apiResource('inspections', InspectionController::class);

    // Deductions
    Route::post('/deductions/{deduction}/approve', [DeductionController::class, 'approve']);    
    Route::apiResource('deductions', DeductionController::class);

    // List refundable deposits (GET)
    Route::get('/deposits/refundable', [RefundController::class, 'refundableDeposits']);

    // Finalize a refund (POST) - deposit ID required
    Route::post('/deposits/{deposit}/finalize-refund', [RefundController::class, 'finalizeDepositFromDeposit']);

    // Audit Logs
    Route::apiResource('audit-logs', AuditLogController::class);
});