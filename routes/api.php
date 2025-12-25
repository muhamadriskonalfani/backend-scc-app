<?php

use App\Http\Controllers\Mobile\Auth\AuthController as MobileAuthController;

use App\Http\Controllers\Mobile\TracerStudy\TracerStudyController;

use App\Http\Controllers\Mobile\Profile\ProfileController;

use App\Http\Controllers\Mobile\Campus\CampusInformationController;

use App\Http\Controllers\Admin\JobVacancy\JobVacancyController as AdminJobVacancyController;
use App\Http\Controllers\Mobile\JobVacancy\JobVacancyController as MobileJobVacancyController;

use App\Http\Controllers\Admin\Apprenticeship\ApprenticeshipController as AdminApprenticeshipController;
use App\Http\Controllers\Mobile\Apprenticeship\ApprenticeshipController as MobileApprenticeshipController;

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

Route::prefix('mobile')
    ->middleware(['auth:sanctum', 'status:active'])
    ->group(function () {

    // Semua user mobile (student & alumni)
    Route::get('/jobvacancy', [MobileJobVacancyController::class, 'index']);
    Route::get('/jobvacancy/{id}', [MobileJobVacancyController::class, 'show']);

    // Alumni only
    Route::middleware('role:alumni')->group(function () {
        Route::post('/jobvacancy', [MobileJobVacancyController::class, 'store']);
        Route::put('/jobvacancy/{id}', [MobileJobVacancyController::class, 'update']);
        Route::get('/my-jobvacancy', [MobileJobVacancyController::class, 'myJobvacancy']);
    });
});

Route::prefix('admin')
    ->middleware(['auth:sanctum', 'role:admin,super_admin'])
    ->group(function () {

    Route::get('/jobvacancy', [AdminJobVacancyController::class, 'index']);
    Route::get('/jobvacancy/{id}', [AdminJobVacancyController::class, 'show']);

    Route::put('/jobvacancy/{id}/approve', [AdminJobVacancyController::class, 'approve']);
    Route::put('/jobvacancy/{id}/reject', [AdminJobVacancyController::class, 'reject']);
});


Route::prefix('mobile')
    ->middleware(['auth:sanctum', 'status:active'])
    ->group(function () {

    // Student & Alumni
    Route::get('/apprenticeships', [MobileApprenticeshipController::class, 'index']);
    Route::get('/apprenticeships/{id}', [MobileApprenticeshipController::class, 'show']);

    // Alumni only
    Route::middleware('role:alumni')->group(function () {
        Route::post('/apprenticeships', [MobileApprenticeshipController::class, 'store']);
        Route::put('/apprenticeships/{id}', [MobileApprenticeshipController::class, 'update']);
        Route::get('/my-apprenticeships', [MobileApprenticeshipController::class, 'myApprenticeships']);
    });
});

Route::prefix('admin')
    ->middleware(['auth:sanctum', 'role:admin,super_admin'])
    ->group(function () {

    Route::get('/apprenticeships', [AdminApprenticeshipController::class, 'index']);
    Route::get('/apprenticeships/{id}', [AdminApprenticeshipController::class, 'show']);

    Route::put('/apprenticeships/{id}/approve', [AdminApprenticeshipController::class, 'approve']);
    Route::put('/apprenticeships/{id}/reject', [AdminApprenticeshipController::class, 'reject']);
});
