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
    // Resource routes with implicit model binding
    Route::apiResource('environments', EnvironmentController::class);
    Route::apiResource('workspaces', WorkspaceController::class);
    Route::apiResource('collections', CollectionController::class);

    // Nested collection items routes
    Route::get('/collections/{collectionId}/items', [CollectionItemController::class, 'index']);
    Route::post('/collections/{collectionId}/items', [CollectionItemController::class, 'store']);
    Route::put('/collections/items/{collectionItem}', [CollectionItemController::class, 'update']);
    Route::delete('/collections/items/{collectionItem}', [CollectionItemController::class, 'destroy']);
    Route::post('/collections/{collectionId}/reorder', [CollectionItemController::class, 'reorder']);
});
