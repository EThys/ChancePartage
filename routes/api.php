<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\TicketController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


//Routes for authentification
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
//Il faut etre AUTHETIFIER
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::get('/users/all', [AuthController::class, 'index'])->middleware('auth:sanctum');
Route::get('/user/{id}', [AuthController::class, 'show'])->middleware('auth:sanctum');

// Routes pour les événements
Route::get('/events/all', [EventController::class, 'index'])->middleware('auth:sanctum');
Route::post('/events', [EventController::class, 'createEvent'])->middleware('auth:sanctum');
Route::put('/events/{eventId}', [EventController::class, 'updateEvent'])->middleware('auth:sanctum');
Route::delete('/events/{eventId}', [EventController::class, 'deleteEvent'])->middleware('auth:sanctum');
Route::get('/events/{eventId}', [EventController::class, 'showEvent'])->middleware('auth:sanctum');

// Routes pour les réservations
Route::get('/reservations/all', [ReservationController::class, 'index'])->middleware('auth:sanctum');
Route::post('/events/{eventId}/reservation', [ReservationController::class, 'createReservation'])->middleware('auth:sanctum');
Route::get('/reservations/{reservationId}', [ReservationController::class, 'showReservation'])->middleware('auth:sanctum');
Route::put('/reservations/{reservationId}', [ReservationController::class, 'updateReservation'])->middleware('auth:sanctum');
Route::delete('/reservations/{reservationId}', [ReservationController::class, 'deleteReservation'])->middleware('auth:sanctum');

// Routes pour les tickets
Route::get('/tickets/all', [TicketController::class, 'index'])->middleware('auth:sanctum');