<?php

use App\Interface\Http\Controllers\Api\AuthController;
use App\Interface\Http\Controllers\Api\ProjectController;
use Illuminate\Support\Facades\Route;
use App\Interface\Http\Controllers\Api\TaskController;
use App\Interface\Http\Controllers\Api\TimerController;
use App\Interface\Http\Controllers\Api\GoalController;

Route::middleware(['auth:sanctum', 'throttle:20,1'])->prefix('projects')->group(function () {
    Route::post('/', [ProjectController::class, 'store']);
    Route::post('{project}/tasks', [TaskController::class, 'store']);
    Route::get('{project}/tasks', [TaskController::class, 'index']);
    Route::get('{project}/tasks/{task}', [TaskController::class, 'show']);
    Route::patch('{project}/tasks/{task}', [TaskController::class, 'update']);
    Route::delete('{project}/tasks/{task}', [TaskController::class, 'destroy']);
    Route::post('{project}/complete', [ProjectController::class, 'complete']);
    Route::delete('{project}', [ProjectController::class, 'destroy']);
    Route::get('/', [ProjectController::class, 'index']);
});

Route::middleware('auth:sanctum')->prefix('tasks')->group(function () {
    Route::post('{task}/timers/start', [TimerController::class, 'start'])
        ->name('api.tasks.timers.start');

    Route::post('{task}/timers/pause', [TimerController::class, 'pause'])
        ->name('api.tasks.timers.pause');

    Route::post('{task}/timers/stop', [TimerController::class, 'stop'])
        ->name('api.tasks.timers.stop');

    Route::get('{task}/timers', [TimerController::class, 'index'])
        ->name('api.tasks.timers.index');
});

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

Route::middleware('auth:sanctum')->prefix('projects')->group(function () {
    Route::scopeBindings()->group(function () {
        Route::get('{project}/goals', [GoalController::class, 'index'])->name('api.projects.goals.index');
        Route::post('{project}/goals', [GoalController::class, 'store'])->name('api.projects.goals.store');
        Route::get('{project}/goals/{goal}', [GoalController::class, 'show'])->name('api.projects.goals.show');
        Route::get('{project}/goals/{goal}/progress', [GoalController::class, 'progress'])->name('api.projects.goals.progress');
        Route::post('{project}/goals/{goal}/complete', [GoalController::class, 'complete'])->name('api.projects.goals.complete');
        Route::post('{project}/goals/{goal}/tasks/{task}', [GoalController::class, 'attach'])->name('api.projects.goals.tasks.attach');
        Route::delete('{project}/goals/{goal}/tasks/{task}', [GoalController::class, 'detach'])->name('api.projects.goals.tasks.detach');
    });
});
