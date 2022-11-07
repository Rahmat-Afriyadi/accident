<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ReportController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::controller(ReportController::class)
    ->prefix("reports")
    ->group(function () {
        Route::get("/", "index");
        Route::get("/show/{id}", "show");
        Route::post("/", "store");
        Route::put("/update/{slug}", "update");
        Route::delete("/delete/{id}", "delete");
    });

Route::group(['middleware' => 'api'], function () {

    Route::controller(AuthController::class)->group(function () {
        Route::post('register', 'register');
        Route::post('login', 'login')->name('login');
        Route::post('logout', 'logout');
        Route::post('refresh_token', 'refresh');
        Route::post('me', 'me');

        Route::post(
            '/forgot-password',
            'requestForgotPassword'
        )
            ->middleware('guest')
            ->name('password.email');
        Route::post('/reset-password', 'updatePassword')
            ->middleware('guest')
            ->name('password.update');
        Route::get('/reset-password/{token}', 'getToken')
            ->middleware('guest')
            ->name('password.reset');
    });
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
