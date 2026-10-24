<?php

use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

Route::prefix('tickets')->group(function () {
    Route::get('/stats', [TicketController::class, 'stats']);
    Route::get('/', [TicketController::class, 'index']);
    Route::post('/', [TicketController::class, 'store']);
    Route::patch('/{id}', [TicketController::class, 'update']);
});
