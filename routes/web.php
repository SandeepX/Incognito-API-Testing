<?php

use App\Http\Controllers\Api\CollectionController;
use App\Http\Controllers\Api\CollectionItemController;
use App\Http\Controllers\Api\EnvironmentController;
use App\Http\Controllers\Api\WorkspaceController;
use App\Http\Controllers\ApiTesterController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ApiTesterController::class, 'index']);
Route::post('/api-tester/proxy', [ApiTesterController::class, 'proxy']);

Route::prefix('api')->group(function () {
    Route::get('/environments', [EnvironmentController::class, 'index']);
    Route::post('/environments', [EnvironmentController::class, 'store']);
    Route::put('/environments/{id}', [EnvironmentController::class, 'update']);
    Route::delete('/environments/{id}', [EnvironmentController::class, 'destroy']);

    Route::get('/workspaces', [WorkspaceController::class, 'index']);
    Route::post('/workspaces', [WorkspaceController::class, 'store']);
    Route::put('/workspaces/{id}', [WorkspaceController::class, 'update']);
    Route::delete('/workspaces/{id}', [WorkspaceController::class, 'destroy']);

    Route::get('/collections', [CollectionController::class, 'index']);
    Route::post('/collections', [CollectionController::class, 'store']);
    Route::put('/collections/{id}', [CollectionController::class, 'update']);
    Route::delete('/collections/{id}', [CollectionController::class, 'destroy']);

    Route::get('/collections/{collectionId}/items', [CollectionItemController::class, 'index']);
    Route::post('/collections/{collectionId}/items', [CollectionItemController::class, 'store']);
    Route::put('/collections/items/{id}', [CollectionItemController::class, 'update']);
    Route::delete('/collections/items/{id}', [CollectionItemController::class, 'destroy']);
    Route::post('/collections/{collectionId}/reorder', [CollectionItemController::class, 'reorder']);
});
