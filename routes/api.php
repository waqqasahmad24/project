<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProviderController;
use App\Http\Controllers\Api\BookingController;

Route::get('/providers', [ProviderController::class, 'index']);
Route::get('/providers/{provider}', [ProviderController::class, 'show']);
Route::get('/providers/{provider}/slots', [ProviderController::class, 'getAvailableSlots']);

Route::post('/bookings', [BookingController::class, 'store']);
Route::patch('/bookings/{booking}/status', [BookingController::class, 'updateStatus']);
Route::post('/bookings/{booking}/reschedule', [BookingController::class, 'reschedule']);
