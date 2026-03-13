<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// MOBILE CONTROLLERS
use App\Http\Controllers\Mobile\Auth\AuthController as MobileAuthController;
use App\Http\Controllers\Mobile\Dashboard\DashboardController as MobileDashboardController;
use App\Http\Controllers\Mobile\TracerStudy\TracerStudyController as MobileTracerStudyController;
use App\Http\Controllers\Mobile\Profile\ProfileController as MobileProfileController;
use App\Http\Controllers\Mobile\CampusDirectory\CampusDirectoryController as MobileCampusDirectoryController;
use App\Http\Controllers\Mobile\Campus\CampusInformationController as MobileCampusInformationController;
use App\Http\Controllers\Mobile\JobVacancy\JobVacancyController as MobileJobVacancyController;
use App\Http\Controllers\Mobile\Apprenticeship\ApprenticeshipController as MobileApprenticeshipController;

// ADMIN CONTROLLERS
use App\Http\Controllers\Admin\Auth\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\Dashboard\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\User\UserManagementController as AdminUserManagementController;
use App\Http\Controllers\Admin\JobVacancy\JobVacancyController as AdminJobVacancyController;
use App\Http\Controllers\Admin\Apprenticeship\ApprenticeshipController as AdminApprenticeshipController;
use App\Http\Controllers\Admin\SuperAdmin\AdminManagementController;
use App\Http\Controllers\Admin\TracerStudy\TracerStudyController as AdminTracerStudyController;
use App\Http\Controllers\Admin\Campus\CampusInformationController as AdminCampusInformationController;
use App\Http\Controllers\Admin\Master\FacultyController as AdminFacultyController;
use App\Http\Controllers\Admin\Master\StudyProgramController as AdminStudyProgramController;
use App\Http\Controllers\Admin\Setting\SettingController as AdminSettingController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

/*
|--------------------------------------------------------------------------
| MOBILE AUTH
|--------------------------------------------------------------------------
*/
Route::prefix('mobile')->group(function () {
    Route::get('/register-meta', [MobileAuthController::class, 'registerMeta']);
    Route::post('/register', [MobileAuthController::class, 'register']);
    Route::post('/login', [MobileAuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [MobileAuthController::class, 'logout']);
    });
});

/*
|--------------------------------------------------------------------------
| MOBILE DASHBOARD
|--------------------------------------------------------------------------
*/
Route::prefix('mobile')
    ->middleware(['auth:sanctum', 'status:active', 'role:student,alumni'])
    ->group(function () {

    // Dashboard
    Route::get('/dashboard', [MobileDashboardController::class, 'index']);
});

/*
|--------------------------------------------------------------------------
| MOBILE TRACER STUDY
|--------------------------------------------------------------------------
*/
Route::prefix('mobile')
    ->middleware(['auth:sanctum', 'status:active', 'role:alumni'])
    ->group(function () {
        Route::get('/tracer-study', [MobileTracerStudyController::class, 'index']);
        Route::put('/tracer-study', [MobileTracerStudyController::class, 'update']);
        Route::get('/tracer-study/status', [MobileTracerStudyController::class, 'status']);
    });

/*
|--------------------------------------------------------------------------
| MOBILE PROFILE
|--------------------------------------------------------------------------
*/
Route::prefix('mobile')
    ->middleware(['auth:sanctum', 'status:active', 'role:student,alumni'])
    ->group(function () {
        Route::get('/profile', [MobileProfileController::class, 'show']);
        Route::post('/profile', [MobileProfileController::class, 'store']);
        Route::put('/profile', [MobileProfileController::class, 'update']);
    });

/*
|--------------------------------------------------------------------------
| MOBILE CAMPUS DIRECTORY
|--------------------------------------------------------------------------
*/
Route::prefix('mobile')
    ->middleware(['auth:sanctum', 'status:active', 'role:student,alumni'])
    ->group(function () {

        Route::get('/directory', [MobileCampusDirectoryController::class, 'index']);
        Route::get('/directory/{id}', [MobileCampusDirectoryController::class, 'show']);
    });

/*
|--------------------------------------------------------------------------
| MOBILE CAMPUS INFORMATION
|--------------------------------------------------------------------------
*/
Route::prefix('mobile')
    ->middleware(['auth:sanctum', 'status:active', 'role:student'])
    ->group(function () {
        Route::get('/information-campus', [MobileCampusInformationController::class, 'index']);
        Route::get('/information-campus/{id}', [MobileCampusInformationController::class, 'show']);
    });

/*
|--------------------------------------------------------------------------
| MOBILE JOB VACANCY
|--------------------------------------------------------------------------
*/
Route::prefix('mobile')
    ->middleware(['auth:sanctum', 'status:active', 'role:student,alumni'])
    ->group(function () {

        Route::get('/jobvacancy', [MobileJobVacancyController::class, 'index']);
        Route::get('/jobvacancy/{id}', [MobileJobVacancyController::class, 'show']);

        Route::middleware('role:alumni')->group(function () {
            Route::post('/jobvacancy', [MobileJobVacancyController::class, 'store']);
            Route::put('/jobvacancy/{id}', [MobileJobVacancyController::class, 'update']);
            Route::get('/my-jobvacancy', [MobileJobVacancyController::class, 'myJobvacancy']);
        });
    });

/*
|--------------------------------------------------------------------------
| MOBILE APPRENTICESHIP
|--------------------------------------------------------------------------
*/
Route::prefix('mobile')
    ->middleware(['auth:sanctum', 'status:active', 'role:student,alumni'])
    ->group(function () {

        Route::get('/apprenticeships', [MobileApprenticeshipController::class, 'index']);
        Route::get('/apprenticeships/{id}', [MobileApprenticeshipController::class, 'show']);

        Route::middleware('role:alumni')->group(function () {
            Route::post('/apprenticeships', [MobileApprenticeshipController::class, 'store']);
            Route::put('/apprenticeships/{id}', [MobileApprenticeshipController::class, 'update']);
            Route::get('/my-apprenticeships', [MobileApprenticeshipController::class, 'myApprenticeships']);
        });
    });


/*
|--------------------------------------------------------------------------
| ADMIN AUTH
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->group(function () {
    Route::post('/login', [AdminAuthController::class, 'login']);

    Route::middleware(['auth:sanctum', 'admin.status'])->group(function () {
        Route::post('/logout', [AdminAuthController::class, 'logout']);

        Route::get('/get-master', [AdminAuthController::class, 'getMaster']);
    });
});

/*
|--------------------------------------------------------------------------
| ADMIN DASHBOARD
|--------------------------------------------------------------------------
*/
Route::prefix('admin')
    ->middleware(['auth:sanctum', 'admin.status'])
    ->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index']);
    });

/*
|--------------------------------------------------------------------------
| ADMIN SUPER ADMIN
|--------------------------------------------------------------------------
*/
Route::prefix('admin')
    ->middleware(['auth:sanctum', 'role:super_admin'])
    ->group(function () {

    Route::post('/admins', [AdminManagementController::class, 'registerAdmin']);
    Route::get('/admins', [AdminManagementController::class, 'index']);
    Route::get('/admins/{id}', [AdminManagementController::class, 'show']);
    Route::put('/admins/{id}', [AdminManagementController::class, 'update']);
});

/*
|--------------------------------------------------------------------------
| ADMIN USER MANAGEMENT
|--------------------------------------------------------------------------
*/
Route::prefix('admin')
    ->middleware(['auth:sanctum', 'role:admin', 'admin.status', 'admin.faculty'])
    ->group(function () {

    Route::get('/students', [AdminUserManagementController::class, 'students']);
    Route::get('/alumni', [AdminUserManagementController::class, 'alumni']);

    Route::put('/users/{id}/approve', [AdminUserManagementController::class, 'approve']);
    Route::put('/users/{id}/reject', [AdminUserManagementController::class, 'reject']);

    Route::get('/users/{id}', [AdminUserManagementController::class, 'show']);
});

/*
|--------------------------------------------------------------------------
| ADMIN TRACER STUDY
|--------------------------------------------------------------------------
*/
Route::prefix('admin')
    ->middleware(['auth:sanctum', 'role:super_admin'])
    ->group(function () {
        Route::get('/tracer-studies', [AdminTracerStudyController::class, 'index']);
        Route::get('/tracer-studies/export', [AdminTracerStudyController::class, 'export']);
    });

/*
|--------------------------------------------------------------------------
| ADMIN JOB VACANCY
|--------------------------------------------------------------------------
*/
Route::prefix('admin')
    ->middleware(['auth:sanctum', 'role:admin', 'admin.status', 'admin.faculty'])
    ->group(function () {

        Route::get('/jobvacancy', [AdminJobVacancyController::class, 'index']);
        Route::get('/jobvacancy/{id}', [AdminJobVacancyController::class, 'show']);

        Route::put('/jobvacancy/{id}/approve', [AdminJobVacancyController::class, 'approve']);
        Route::put('/jobvacancy/{id}/reject', [AdminJobVacancyController::class, 'reject']);
        Route::put('/jobvacancy/{id}/end', [AdminJobVacancyController::class, 'end']);
    });

/*
|--------------------------------------------------------------------------
| ADMIN APPRENTICESHIP
|--------------------------------------------------------------------------
*/
Route::prefix('admin')
    ->middleware(['auth:sanctum', 'role:admin', 'admin.status', 'admin.faculty'])
    ->group(function () {

        Route::get('/apprenticeships', [AdminApprenticeshipController::class, 'index']);
        Route::get('/apprenticeships/{id}', [AdminApprenticeshipController::class, 'show']);
        
        Route::put('/apprenticeships/{id}/approve', [AdminApprenticeshipController::class, 'approve']);
        Route::put('/apprenticeships/{id}/reject', [AdminApprenticeshipController::class, 'reject']);
        Route::put('/apprenticeships/{id}/end', [AdminApprenticeshipController::class, 'end']);
    });

/*
|--------------------------------------------------------------------------
| ADMIN CAMPUS INFO
|--------------------------------------------------------------------------
*/
Route::prefix('admin')
    ->middleware(['auth:sanctum', 'role:admin,super_admin', 'admin.status'])
    ->group(function () {

    Route::get('/campus', [AdminCampusInformationController::class, 'index']);
    Route::post('/campus', [AdminCampusInformationController::class, 'store']);

    // Action khusus (letakkan dulu)
    Route::put('/campus/{id}/end', [AdminCampusInformationController::class, 'end']);

    // CRUD standar
    Route::get('/campus/{id}', [AdminCampusInformationController::class, 'show']);
    Route::put('/campus/{id}', [AdminCampusInformationController::class, 'update']);
});

/*
|--------------------------------------------------------------------------
| ADMIN FACULTIES 
|--------------------------------------------------------------------------
*/
Route::prefix('admin')
    ->middleware(['auth:sanctum', 'role:super_admin', 'admin.status'])
    ->group(function () {

    Route::prefix('master')->group(function () {
        Route::get('/faculties', [AdminFacultyController::class, 'index']);
        Route::post('/faculties', [AdminFacultyController::class, 'store']);
        Route::put('/faculties/{id}', [AdminFacultyController::class, 'update']);
        Route::delete('/faculties/{id}', [AdminFacultyController::class, 'destroy']);
    });
});

/*
|--------------------------------------------------------------------------
| ADMIN STUDY PROGRAMS 
|--------------------------------------------------------------------------
*/
Route::prefix('admin/master')
    ->middleware(['auth:sanctum', 'role:super_admin', 'admin.status'])
    ->group(function () {

    Route::get('/study-programs', [AdminStudyProgramController::class, 'index']);
    Route::post('/study-programs', [AdminStudyProgramController::class, 'store']);
    Route::get('/study-programs/{id}', [AdminStudyProgramController::class, 'show']);
    Route::put('/study-programs/{id}', [AdminStudyProgramController::class, 'update']);
    Route::delete('/study-programs/{id}', [AdminStudyProgramController::class, 'destroy']);
});

/*
|--------------------------------------------------------------------------
| ADMIN SETTING  
|--------------------------------------------------------------------------
*/
Route::prefix('admin')
    ->middleware(['auth:sanctum', 'role:super_admin', 'admin.status'])
    ->group(function () {

    Route::prefix('setting')->group(function () {
        Route::get('/users', [AdminSettingController::class, 'index']);
        Route::post('/users', [AdminSettingController::class, 'registerUsers']);
    });
});

// END
