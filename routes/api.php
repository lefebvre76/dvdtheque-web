<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\BoxController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/login', [AuthController::class, 'login'])->name('api.login');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me'])->name('api.me');
    Route::put('/me', [AuthController::class, 'update'])->name('api.me.update');

    Route::post('/boxes', [BoxController::class, 'store'])->name('api.box.bar_code');
    Route::get('/boxes/{box}', [BoxController::class, 'show'])->name('api.box.show');

    Route::get('/me/boxes', [BoxController::class, 'index'])->name('api.box.me');
    Route::post('/me/boxes/{box}', [BoxController::class, 'addToAuthUser'])->name('api.box.me.add');
    Route::delete('/me/boxes/{box}', [BoxController::class, 'deleteFromAuthUser'])->name('api.box.me.remove');
});
