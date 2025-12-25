<?php

use App\Http\Controllers\Admin\Career\CareerController as AdminCareerController;

use App\Http\Controllers\Mobile\Auth\AuthController as MobileAuthController;
use App\Http\Controllers\Mobile\Campus\CampusInformationController;
use App\Http\Controllers\Mobile\Career\CareerController as MobileCareerController;
use App\Http\Controllers\Mobile\Profile\ProfileController;
use App\Http\Controllers\Mobile\TracerStudy\TracerStudyController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('mobile')->group(function () {
    Route::get('/register-meta', [MobileAuthController::class, 'registerMeta']);
    Route::post('/register', [MobileAuthController::class, 'register']);
    Route::post('/login', [MobileAuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [MobileAuthController::class, 'logout']);
    });
});

Route::prefix('mobile')->middleware(['auth:sanctum', 'alumni'])->group(function () {
    // Tracer Study
    Route::get('/tracer-study', [TracerStudyController::class, 'index']);
    Route::put('/tracer-study', [TracerStudyController::class, 'update']);
    Route::get('/tracer-study/status', [TracerStudyController::class, 'status']);
});

Route::middleware('auth:sanctum')->prefix('mobile')->group(function () {
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::post('/profile', [ProfileController::class, 'store']);
    Route::put('/profile', [ProfileController::class, 'update']);
});

Route::prefix('mobile')->middleware('auth:sanctum')->group(function () {
    Route::get('/information-campus', [CampusInformationController::class, 'index']);
    Route::get('/information-campus/{id}', [CampusInformationController::class, 'show']);
});

Route::prefix('mobile')->middleware('auth:sanctum')->group(function () {

    // Semua user mobile (student & alumni)
    Route::get('/careers', [MobileCareerController::class, 'index']);
    Route::get('/careers/{id}', [MobileCareerController::class, 'show']);

    // Alumni only
    Route::middleware('role:alumni')->group(function () {
        Route::post('/careers', [MobileCareerController::class, 'store']);
        Route::put('/careers/{id}', [MobileCareerController::class, 'update']);
        Route::get('/my-careers', [MobileCareerController::class, 'myCareers']);
    });
});

Route::prefix('admin')->middleware(['auth:sanctum', 'role:admin,super_admin'])->group(function () {

    Route::get('/careers', [AdminCareerController::class, 'index']);
    Route::get('/careers/{id}', [AdminCareerController::class, 'show']);

    Route::put('/careers/{id}/approve', [AdminCareerController::class, 'approve']);
    Route::put('/careers/{id}/reject', [AdminCareerController::class, 'reject']);
});

