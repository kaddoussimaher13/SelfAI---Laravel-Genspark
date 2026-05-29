<?php

use App\Http\Controllers\ApiKeyController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\StatsController;
use App\Http\Controllers\ToolsController;
use Illuminate\Support\Facades\Route;

/*
 |--------------------------------------------------------------------
 | Public marketing pages (no auth required)
 |--------------------------------------------------------------------
 */
Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('chat.index')
        : app(LandingController::class)->index();
})->name('welcome');

Route::get('/features', [LandingController::class, 'features'])->name('landing.features');
Route::get('/pricing',  [LandingController::class, 'pricing'])->name('landing.pricing');
Route::get('/about',    [LandingController::class, 'about'])->name('landing.about');

/*
 |--------------------------------------------------------------------
 | Authenticated routes
 |   - tenant       → boots the user's private SQLite DB
 |   - require.api_key → forces onboarding if no Genspark key set
 |--------------------------------------------------------------------
 */
Route::middleware(['auth', 'tenant'])->group(function () {

    // Settings & profile MUST stay reachable even without an API key,
    // otherwise the user can never finish onboarding.
    Route::get('/settings',                                    [SettingsController::class, 'edit'])->name('settings.edit');
    Route::patch('/settings',                                  [SettingsController::class, 'update'])->name('settings.update');

    // --- API Keys CRUD (multi-key auto-failover — THE key feature) ---
    Route::get('/settings/api-keys',                           [ApiKeyController::class, 'index'])->name('apikeys.index');
    Route::post('/settings/api-keys',                          [ApiKeyController::class, 'store'])->name('apikeys.store');
    Route::patch('/settings/api-keys/{apiKey}',                [ApiKeyController::class, 'update'])->name('apikeys.update');
    Route::delete('/settings/api-keys/{apiKey}',               [ApiKeyController::class, 'destroy'])->name('apikeys.destroy');
    Route::post('/settings/api-keys/{apiKey}/primary',         [ApiKeyController::class, 'makePrimary'])->name('apikeys.primary');
    Route::post('/settings/api-keys/{apiKey}/toggle',          [ApiKeyController::class, 'toggleActive'])->name('apikeys.toggle');
    Route::post('/settings/api-keys/{apiKey}/test',            [ApiKeyController::class, 'test'])->name('apikeys.test');

    // --- Projects CRUD ---
    Route::get('/settings/projects',                           [ProjectController::class, 'index'])->name('projects.index');
    Route::post('/settings/projects',                          [ProjectController::class, 'store'])->name('projects.store');
    Route::patch('/settings/projects/{project}',               [ProjectController::class, 'update'])->name('projects.update');
    Route::delete('/settings/projects/{project}',              [ProjectController::class, 'destroy'])->name('projects.destroy');
    Route::post('/settings/projects/{project}/default',        [ProjectController::class, 'makeDefault'])->name('projects.default');

    Route::get('/profile',                                     [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',                                   [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile',                                  [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Everything below requires an API key — middleware redirects to /settings if missing.
    Route::middleware('require.api_key')->group(function () {

        Route::get('/dashboard', fn () => redirect()->route('chat.index'))->name('dashboard');

        // --- Chat ---
        Route::get('/chat',                                    [ChatController::class, 'index'])->name('chat.index');
        Route::get('/chat/{conversation}',                     [ChatController::class, 'index'])->name('chat.show');
        Route::post('/chat',                                   [ChatController::class, 'send'])->name('chat.send');
        Route::post('/chat/{conversation}/send',               [ChatController::class, 'send'])->name('chat.send.existing');
        Route::post('/chat/{conversation}/regenerate',         [ChatController::class, 'regenerate'])->name('chat.regenerate');
        Route::patch('/chat/{conversation}/messages/{message}',[ChatController::class, 'editMessage'])->name('chat.message.edit');

        // --- Conversations management ---
        Route::get('/conversations',                           [ConversationController::class, 'index'])->name('conversations.index');
        Route::patch('/conversations/{conversation}',          [ConversationController::class, 'update'])->name('conversations.update');
        Route::delete('/conversations/{conversation}',         [ConversationController::class, 'destroy'])->name('conversations.destroy');

        // --- AI Tools (image / video / audio / search / docs / …) ---
        Route::get('/tools',                                   [ToolsController::class, 'index'])->name('tools.index');
        Route::post('/tools/run/{tool}',                       [ToolsController::class, 'run'])->name('tools.run')
            ->where('tool', '[a-z_]+');

        // --- Usage statistics dashboard ---
        Route::get('/stats',                                   [StatsController::class, 'index'])->name('stats.index');
    });
});

require __DIR__ . '/auth.php';
