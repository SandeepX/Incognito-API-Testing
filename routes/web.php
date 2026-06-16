<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Api\CollectionController;
use App\Http\Controllers\Api\CollectionItemController;
use App\Http\Controllers\Api\EnvironmentController;
use App\Http\Controllers\Api\WorkspaceController;
use App\Http\Controllers\Api\WorkspaceMemberController;
use App\Http\Controllers\ApiTesterController;
use App\Http\Controllers\CollectionDocumentationController;
use Illuminate\Support\Facades\Route;

// Auth Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store']);

    // Password Reset
    Route::get('/forgot-password', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'create'])
        ->name('password.request');
    Route::post('/forgot-password', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'store'])
        ->name('password.email');
    Route::get('/reset-password/{token}', [App\Http\Controllers\Auth\ResetPasswordController::class, 'create'])
        ->name('password.reset');
    Route::post('/reset-password', [App\Http\Controllers\Auth\ResetPasswordController::class, 'store'])
        ->name('password.update');
});

Route::post('/logout', [LoginController::class, 'destroy'])->name('logout')->middleware('auth');

// Workspace invite acceptance (accessible without full auth, but user must be logged in)
Route::get('/invite/{token}', [WorkspaceMemberController::class, 'acceptInvite'])->name('invite.accept');

// Protected Routes
Route::middleware('auth')->group(function () {
    Route::get('/', [ApiTesterController::class, 'index']);

    Route::post('/api-tester/proxy', [ApiTesterController::class, 'proxy']);

    Route::get('/collections/{collection}/docs', [CollectionDocumentationController::class, 'show'])->name('collection.docs');

    Route::prefix('api')->group(function () {
        // Resource routes with implicit model binding
        Route::apiResource('environments', EnvironmentController::class);
        Route::apiResource('workspaces', WorkspaceController::class);
        Route::apiResource('collections', CollectionController::class);

        // Workspace members & invites
        Route::get('/workspaces/{workspace}/members', [WorkspaceMemberController::class, 'index']);
        Route::post('/workspaces/{workspace}/invites', [WorkspaceMemberController::class, 'createInvite']);
        Route::delete('/workspaces/{workspace}/members/{user}', [WorkspaceMemberController::class, 'removeMember']);

        // Nested collection items routes
        Route::get('/collections/{collectionId}/items', [CollectionItemController::class, 'index']);
        Route::post('/collections/{collectionId}/items', [CollectionItemController::class, 'store']);
        Route::put('/collections/items/{collectionItem}', [CollectionItemController::class, 'update']);
        Route::delete('/collections/items/{collectionItem}', [CollectionItemController::class, 'destroy']);
        Route::post('/collections/{collectionId}/reorder', [CollectionItemController::class, 'reorder']);
    });
});
