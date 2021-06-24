<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BarberController;

Route::get('auth/401', [AuthController::class, 'unauthenticated'])->name('login');
Route::post('auth/user', [AuthController::class, 'create']);
Route::post('auth/login', [AuthController::class, 'login']);
Route::post('auth/logout', [AuthController::class, 'logout']);
Route::post('auth/refresh', [AuthController::class, 'refresh']);

Route::get('user', [UserController::class, 'show']);
Route::put('user', [UserController::class, 'update']);
Route::post('user/avatar', [UserController::class, 'avatar']);
Route::get('user/favorites', [UserController::class, 'favorites']);
Route::post('user/favorite', [UserController::class, 'toggleFavorite']);
Route::get('user/appointments', [UserController::class, 'appointments']);

Route::get('barbers', [BarberController::class, 'index']);
Route::get('barber/{id}', [BarberController::class, 'show']);
Route::post('barber/{id}/appointment', [BarberController::class, 'addAppointment']);
Route::get('search', [BarberController::class, 'search']);

Route::get('ping', function () {
    return ['pong' => true];
});
