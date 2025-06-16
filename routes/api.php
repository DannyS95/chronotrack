<?php

use App\Interface\Http\Controllers\Api\AuthController;
use App\Interface\Http\Controllers\Api\ProjectController;
use Illuminate\Support\Facades\Route;
use App\Interface\Http\Controllers\Api\TaskController;
use App\Interface\Http\Controllers\Api\TimerController;

Route::middleware(['auth:sanctum', 'throttle:10,1'])->prefix('projects')->group(function () {
    Route::post('/', [ProjectController::class, 'store']);
    Route::post('{project}/tasks', [TaskController::class, 'store']);
    Route::get('/', [ProjectController::class, 'index']);
});


Route::prefix('tasks')->group(function () {
    Route::post('{task}/timers/start', [TimerController::class, 'start']);
    Route::post('{task}/timers/stop', [TimerController::class, 'stop']);
});

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
});
