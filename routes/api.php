<?php

use App\Http\Controllers\Mobile\Auth\AuthController as MobileAuthController;
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
