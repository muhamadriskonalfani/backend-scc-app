<?php

use App\Http\Controllers\Mobile\Auth\AuthController as MobileAuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('mobile')->group(function () {
    Route::get('/register-meta', [MobileAuthController::class, 'registerMeta']);
    Route::post('/register', [MobileAuthController::class, 'register']);
});
