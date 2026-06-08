<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\SpaceController;
use App\Http\Controllers\StoryController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ResearchController;
use App\Http\Controllers\EventProcessorController;

// ─── Auth ─────────────────────────────────────────────────────────────────────
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login',    [AuthController::class, 'login']);

// ─── Internal cron endpoints (protected by token, not Sanctum) ──────────────
Route::get('/internal/process-events',  [EventProcessorController::class, 'handle']);
Route::post('/internal/process-events', [EventProcessorController::class, 'handle']);
Route::get('/internal/status',          [EventProcessorController::class, 'status']);

// ─── Protected ────────────────────────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    Route::get('/auth/me',      [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // Stories — multiple per user
    Route::get('/stories',            [StoryController::class, 'index']);
    Route::post('/stories',           [StoryController::class, 'store']);
    Route::get('/stories/{story}',    [StoryController::class, 'show']);
    Route::delete('/stories/{story}', [StoryController::class, 'destroy']);

    // Feed
    Route::get('/stories/{story}/feed', [FeedController::class, 'index']);

    // Profiles
    Route::get('/stories/{story}/agents',         [ProfileController::class, 'index']);
    Route::get('/stories/{story}/agents/{agent}', [ProfileController::class, 'show']);

    // Spaces
    Route::get('/stories/{story}/spaces',         [SpaceController::class, 'index']);
    Route::get('/stories/{story}/spaces/{space}', [SpaceController::class, 'show']);

    // Posts
    Route::get('/stories/{story}/posts/{post}', [PostController::class, 'show']);

    // Research
    Route::get('/stories/{story}/research',                        [ResearchController::class, 'index']);
    Route::get('/stories/{story}/research/event/{sequence}',       [ResearchController::class, 'event']);
    Route::get('/stories/{story}/research/agent/{agent}',          [ResearchController::class, 'agent']);
});
