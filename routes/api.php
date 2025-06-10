<?php

use Illuminate\Support\Facades\Route;
use App\Interface\Http\Requests\Api\TaskController;
use App\Interface\Http\Requests\Api\TimerController;
use App\Interface\Http\Controllers\Api\ProjectController;

Route::prefix('projects')->group(function () {
    Route::post('/', [ProjectController::class, 'store']);
    Route::post('{project}/tasks', [TaskController::class, 'store']);
});

Route::prefix('tasks')->group(function () {
    Route::post('{task}/timers/start', [TimerController::class, 'start']);
    Route::post('{task}/timers/stop', [TimerController::class, 'stop']);
});
