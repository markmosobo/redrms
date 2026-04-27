<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\TerminationRequestController;
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
Route::middleware('auth:api')->post('/logout', [AuthController::class, 'logout']);

Route::middleware(['auth:api', 'force.password.change'])->group(function () {

    // Current logged-in user info
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::get('/landlords', [UserController::class, 'landlords']);
    Route::get('/landlords/{landlord}/properties', [PropertyController::class, 'landlordProperties']);
    Route::post('/landlords/{landlord}/properties', [PropertyController::class, 'storeProperty']);    

    Route::get('/managers', [UserController::class, 'managers']);
    Route::get('/managers/{manager}/properties', [PropertyController::class, 'managerProperties']);
    Route::post('/managers/{manager}/assign-properties', [PropertyController::class, 'assignToManager']);

    Route::get('/tenants', [UserController::class, 'tenants']);

    Route::apiResource('users', UserController::class);

    Route::apiResource('properties', PropertyController::class);
    Route::get('/properties/{property}/units', [UnitController::class, 'unitsByProperty']);
    Route::post('/properties/{property}/units', [UnitController::class, 'storeUnit']);

    Route::get('/units/vacant', [UnitController::class, 'vacant']);
    Route::get('/units/{unit}/tenancy', [TenancyController::class, 'showTenancy']);
    Route::post('/units/{unit}/tenancy', [TenancyController::class, 'storeTenancy']);
    Route::apiResource('units', UnitController::class);

    Route::apiResource('tenancies', TenancyController::class);
    Route::put('/tenancies/{tenancy}', [TenancyController::class, 'updateTenancy']);
    Route::post('/tenancies/{tenancy}/terminate', [TenancyController::class, 'terminate']);
    Route::post('/tenancies/assign', [TenancyController::class, 'assign']);

    Route::post('/deposits/{deposit}/receive', [DepositController::class, 'receive']);
    Route::get('/refunds/pending', [RefundController::class, 'refundableDeposits']); 
    Route::get('/refunds/finalized', [RefundController::class, 'finalizedRefunds']); 
    Route::post('/refunds/{refund}/finalize', [RefundController::class, 'finalize']);
    Route::post('/refunds/{refund}/pay', [RefundController::class, 'pay']);
    Route::apiResource('deposits', DepositController::class);

    Route::post('/inspections/complete', [InspectionController::class, 'completeInspection']);
    Route::get('/inspections/by-inspection', [InspectionController::class, 'byInspection']);
    Route::apiResource('inspections', InspectionController::class);

    Route::post('/deductions/{deduction}/approve', [DeductionController::class, 'approve']);    
    Route::post('/deductions/{deduction}/reject', [DeductionController::class, 'reject']);
    Route::apiResource('deductions', DeductionController::class);

    Route::get('/audit-logs', [AuditLogController::class, 'index']);
    Route::get('/audit-logs/{id}', [AuditLogController::class, 'show']);

    Route::get('/dashboard',[DashboardController::class, 'index']); 

    Route::get('/notifications/unread', [NotificationController::class, 'index']);
    Route::get('/notifications', [NotificationController::class, 'all']);
    Route::get('/admin/notifications', [NotificationController::class, 'adminAll']);

    Route::post('/notifications', [NotificationController::class, 'store']);

    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);

    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']); 

    Route::get('/my-active-tenancy', [TenancyController::class, 'myActiveTenancy']);
    Route::get('/my-termination-request', [TerminationRequestController::class, 'myRequest']);

    Route::post('/termination-requests', [TerminationRequestController::class, 'store']);
    Route::get('/termination-requests', [TerminationRequestController::class, 'index']);
    Route::get('/my-termination-requests', [TerminationRequestController::class, 'myRequests']);

    Route::post('/termination-requests/{terminationRequest}/approve', [TerminationRequestController::class, 'approve']);
    Route::post('/termination-requests/{terminationRequest}/reject', [TerminationRequestController::class, 'reject']);    
    
});