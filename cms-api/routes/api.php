<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/login', [AuthController::class, 'login']);

// Authenticated routes (require valid Sanctum token)
Route::middleware(['auth:sanctum', 'check.token'])->group(function () {
    // Authentication
    Route::post('/logout', [AuthController::class, 'logout']);

    // Article routes (accessible to all authenticated users)
    Route::get('/articles', [ArticleController::class, 'index']);
    Route::post('/articles', [ArticleController::class, 'store']);          // Create new article
    Route::get('/articles/{id}', [ArticleController::class, 'show']);       // Show specific article
    Route::put('/articles/{id}', [ArticleController::class, 'update']);     // Update article
    Route::delete('/articles/{id}', [ArticleController::class, 'destroy']); // Delete article


    // Admin-only routes (require admin role)
    Route::middleware('admin')->group(function () {

        Route::get('/categories', [CategoryController::class, 'index']);
        Route::post('/categories', [CategoryController::class, 'store']);
        Route::get('/categories/{id}', [CategoryController::class, 'show']);
        Route::put('/categories/{id}', [CategoryController::class, 'update']);
        Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);
    });

});
