<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\MonolithController;
use App\Http\Controllers\Web\SessionAuthController;

Route::middleware('guest')->group(function () {
    Route::get('/login', [SessionAuthController::class, 'show'])->name('login');
    Route::get('/register', [SessionAuthController::class, 'show'])->name('register');
    Route::post('/login', [SessionAuthController::class, 'login'])->name('login.attempt');
    Route::post('/register', [SessionAuthController::class, 'register'])->name('register.attempt');
});

Route::middleware('auth')->group(function () {
    Route::get('/', [MonolithController::class, 'home'])->name('home');
    Route::post('/logout', [SessionAuthController::class, 'logout'])->name('logout');

    Route::get('/stories', [MonolithController::class, 'stories'])->name('stories.index');
    Route::post('/stories', [MonolithController::class, 'createStory'])->name('stories.store');
    Route::delete('/stories/{story}', [MonolithController::class, 'deleteStory'])->name('stories.destroy');

    Route::get('/stories/{story}/feed', [MonolithController::class, 'feed'])->name('feed.show');
    Route::get('/stories/{story}/timeline', [MonolithController::class, 'timeline'])->name('timeline.show');
    Route::get('/stories/{story}/agents', [MonolithController::class, 'agents'])->name('agents.index');
    Route::get('/stories/{story}/agents/{agent}', [MonolithController::class, 'profile'])->name('agents.show');

    Route::get('/stories/{story}/spaces', [MonolithController::class, 'spaces'])->name('spaces.index');
    Route::get('/stories/{story}/spaces/{space}', [MonolithController::class, 'space'])->name('spaces.show');
    Route::post('/stories/{story}/spaces/{space}/generate', [MonolithController::class, 'generateSpace'])->name('spaces.generate');

    Route::get('/stories/{story}/research', [MonolithController::class, 'research'])->name('research.show');
    Route::get('/stories/{story}/posts/{post}', [MonolithController::class, 'post'])->name('posts.show');
});

Route::fallback(function () {
    return auth()->check() ? redirect()->route('stories.index') : redirect()->route('login');
});
