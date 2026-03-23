<?php

use App\Http\Controllers\Api\ChatbotController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware(['api.key', 'throttle:60,1'])->group(function () {
    Route::get('/projects', [ChatbotController::class, 'projects']);
    Route::get('/rooms', [ChatbotController::class, 'availableRooms']);
    Route::get('/rooms/{unit_code}', [ChatbotController::class, 'roomDetail']);
   
    Route::post('/appointments', [ChatbotController::class, 'bookAppointment'])->middleware('throttle:10,1');
    Route::post('/appointments/{unit_code}/cancel', [ChatbotController::class, 'cancelAppointment'])->middleware('throttle:10,1');
});
