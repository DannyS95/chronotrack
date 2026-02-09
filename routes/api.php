<?php

use App\Interface\Http\Controllers\Api\AuthController;
use App\Interface\Http\Controllers\Api\GoalController;
use App\Interface\Http\Controllers\Api\TaskController;
use App\Interface\Http\Controllers\Api\TimerController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:auth-login')->post('/login', [AuthController::class, 'login']);
Route::middleware('throttle:auth-register')->post('/register', [AuthController::class, 'register']);

Route::middleware(['auth:sanctum', 'throttle:auth-session'])->group(function () {
    Route::get('/me', [AuthController::class, 'me'])->name('api.auth.me');
    Route::post('/logout', [AuthController::class, 'logout'])->name('api.auth.logout');
});

Route::middleware(['auth:sanctum', 'throttle:goals'])
    ->prefix('daily-goals')
    ->name('daily-goals.')
    ->group(function () {
        Route::get('/', [GoalController::class, 'index'])->name('index');
        Route::post('/', [GoalController::class, 'store'])->name('store');
        Route::get('{goal}', [GoalController::class, 'show'])->name('show');
        Route::get('{goal}/progress', [GoalController::class, 'progress'])->name('progress');
        Route::post('{goal}/complete', [GoalController::class, 'complete'])->name('complete');

        Route::middleware('throttle:tasks')
            ->prefix('{goal}/tasks')
            ->name('tasks.')
            ->group(function () {
                Route::post('/', [TaskController::class, 'storeForGoal'])->name('store');
                Route::get('/', [TaskController::class, 'indexForGoal'])->name('index');
            });
    });

Route::middleware(['auth:sanctum', 'throttle:tasks'])->prefix('tasks')->name('tasks.')->group(function () {
    Route::get('{task}', [TaskController::class, 'show'])->name('show');
    Route::patch('{task}', [TaskController::class, 'update'])->name('update');
    Route::delete('{task}', [TaskController::class, 'destroy'])->name('destroy');
});

Route::middleware(['auth:sanctum', 'throttle:timer-actions'])->prefix('tasks')->group(function () {
    Route::post('{task}/timers/start', [TimerController::class, 'start'])
        ->name('api.tasks.timers.start');

    Route::post('{task}/timers/pause', [TimerController::class, 'pause'])
        ->name('api.tasks.timers.pause');

    Route::post('{task}/timers/stop', [TimerController::class, 'stop'])
        ->name('api.tasks.timers.stop');

    Route::get('{task}/timers', [TimerController::class, 'index'])
        ->name('api.tasks.timers.index');
});

Route::middleware(['auth:sanctum', 'throttle:timers'])->prefix('timers')->group(function () {
    Route::post('stop', [TimerController::class, 'stopCurrent'])
        ->name('api.timers.stop-current');
    Route::get('active', [TimerController::class, 'active'])
        ->name('api.timers.active');
});
