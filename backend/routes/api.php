<?php

use App\Http\Controllers\AgentTaskController;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

Route::prefix('tickets')->group(function () {
    Route::get('/stats', [TicketController::class, 'stats']);
    Route::get('/', [TicketController::class, 'index']);
    Route::post('/', [TicketController::class, 'store']);
    Route::patch('/{id}', [TicketController::class, 'update']);

    Route::prefix('{ticket}/agent-tasks')->group(function () {
        Route::get('/', [AgentTaskController::class, 'index']);
        Route::post('/', [AgentTaskController::class, 'store']);
        Route::get('/{agentTask}', [AgentTaskController::class, 'show']);
        Route::post('/{agentTask}/execute', [AgentTaskController::class, 'execute']);
    });
});
