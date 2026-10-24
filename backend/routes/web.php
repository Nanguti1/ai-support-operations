<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'name' => 'Mini Ticket Tracker API',
        'version' => '1.0.0',
        'laravel_version' => app()->version(),
        'description' => 'REST API for ticket management',
        'api_base_url' => url('/api'),
        'endpoints' => [
            'list_tickets' => [
                'method' => 'GET',
                'path' => '/api/tickets',
                'description' => 'List tickets with pagination and optional status filtering',
            ],
            'create_ticket' => [
                'method' => 'POST',
                'path' => '/api/tickets',
                'description' => 'Create a new ticket',
            ],
            'update_ticket' => [
                'method' => 'PATCH',
                'path' => '/api/tickets/{id}',
                'description' => 'Update a ticket (title, description, status, priority). Status enum: open, in_progress, closed. Priority enum: low, medium, high',
            ],
            'ticket_stats' => [
                'method' => 'GET',
                'path' => '/api/tickets/stats',
                'description' => 'Get ticket counts grouped by status',
            ],
        ],
        'documentation' => 'https://github.com/Nanguti1/mini-ticket-tracker',
    ]);
});
