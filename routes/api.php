<?php

use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\LogoutController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\TaskController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/register', [RegisterController::class, 'store']);
Route::post('/auth/login', [LoginController::class, 'check']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::delete('/auth/logout', [LogoutController::class, 'destroy']);

    Route::prefix('projects')->group(function () {
        Route::get('/', [ProjectController::class, 'index']);
        Route::post('/create', [ProjectController::class, 'store']);
        Route::get('/{id}/show', [ProjectController::class, 'show']);
        Route::put('/{id}/edit', [ProjectController::class, 'update']);
        Route::delete('/{id}/delete', [ProjectController::class, 'destroy']);

        Route::prefix('/{projectId}/tasks')->group(function () {
            Route::get('/', [TaskController::class, 'index']);
            Route::post('/create', [TaskController::class, 'store']);
            Route::get('/{taskId}/show', [TaskController::class, 'show']);
            Route::put('/{taskId}/edit', [TaskController::class, 'update']);
            Route::patch('/{taskId}/status-done', [TaskController::class, 'updateStatusToDone']);
            Route::delete('/{taskId}/delete', [TaskController::class, 'destroy']);
        });
    });

});
