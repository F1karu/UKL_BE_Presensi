<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\LogoutController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AttendanceController;

    //ROUTE AUTH
    Route::post('/users', RegisterController::class);
    Route::post('/auth/login', LoginController::class);
    Route::get('/auth/logout', LogoutController::class);

    //ROUTE USER
    Route::get('/userr', [UserController::class, 'index']);
    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::delete('/delete/{id}', [UserController::class, 'delete']);
    Route::put('/users/{id}', [RegisterController::class, 'update']);

    // ROUTE ABSEN
    Route::post('/attendance', [AttendanceController::class, 'hadir']);
    Route::get('/attendance/history/{user_id}', [AttendanceController::class, 'history']);
    Route::get('/attendance/summary/{user_id}', [AttendanceController::class, 'summary']);
    Route::get('/attendance/summary/{user_id}/{monthYear}', [AttendanceController::class, 'summary']);
    Route::post('/attendance/analysis', [AttendanceController::class, 'analysis']);
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
