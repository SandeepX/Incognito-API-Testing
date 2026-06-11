<?php

use App\Http\Controllers\ApiTesterController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ApiTesterController::class, 'index']);
Route::post('/api-tester/proxy', [ApiTesterController::class, 'proxy']);
