<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\BoxController;
use App\Http\Controllers\API\LoanController;
use App\Http\Controllers\API\MovieController;
use App\Http\Middleware\AcceptLanguageMiddleware;

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
Route::middleware(AcceptLanguageMiddleware::class)->group(function () {
    Route::post('/login', [AuthController::class, 'login'])->name('api.login');
    Route::post('/register', [AuthController::class, 'register'])->name('api.register');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');
        Route::get('/me', [AuthController::class, 'me'])->name('api.me');
        Route::put('/me', [AuthController::class, 'update'])->name('api.me.update');

        Route::post('/boxes', [BoxController::class, 'store'])->name('api.box.bar_code');
        Route::get('/boxes/{box}', [BoxController::class, 'show'])->name('api.box.show');

        Route::get('/me/boxes', [BoxController::class, 'index'])->name('api.box.me');
        Route::post('/me/boxes/{box}', [BoxController::class, 'addToAuthUser'])->name('api.box.me.add');
        Route::delete('/me/boxes/{box}', [BoxController::class, 'deleteFromAuthUser'])->name('api.box.me.remove');

        Route::get('/me/movies', [MovieController::class, 'index'])->name('api.movies.me');
        Route::get('/movies/{box}', [MovieController::class, 'show'])->name('api.movies.show');

        Route::get('/loans', [LoanController::class, 'index'])->name('api.loan.index');
        Route::get('/loans/{loan}', [LoanController::class, 'show'])->name('api.loan.show');
        Route::post('/loans', [LoanController::class, 'store'])->name('api.loan.store');
        Route::put('/loans/{loan}', [LoanController::class, 'update'])->name('api.loan.update');
        Route::delete('/loans/{loan}', [LoanController::class, 'delete'])->name('api.loan.delete');
    });
});
