<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTicketRequest;
use App\Http\Requests\UpdateTicketRequest;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'status' => ['nullable', 'in:open,in_progress,closed'],
            'page' => ['nullable', 'integer', 'min:1'],
            'limit' => ['nullable', 'integer', 'min:1'],
        ]);

        $status = $request->query('status');
        $page = (int) $request->query('page', 1);
        $limit = min((int) $request->query('limit', 10), 100);

        $query = Ticket::query();

        if ($status) {
            $query->where('status', $status);
        }

        $tickets = $query->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->paginate($limit, ['*'], 'page', $page);

        return response()->json($tickets);
    }

    public function store(StoreTicketRequest $request): JsonResponse
    {
        $ticket = Ticket::create([
            'title' => $request->title,
            'description' => $request->description,
            'status' => $request->status ?? 'open',
            'priority' => $request->priority ?? 'medium',
        ]);

        return response()->json($ticket, 201);
    }

    public function update(UpdateTicketRequest $request, int $id): JsonResponse
    {
        $ticket = Ticket::find($id);

        if (!$ticket) {
            return response()->json(['message' => 'Ticket not found'], 404);
        }

        $data = [];

        if ($request->filled('status')) {
            $data['status'] = $request->status;
        }

        if ($request->filled('priority')) {
            $data['priority'] = $request->priority;
        }

        $ticket->update($data);

        return response()->json($ticket);
    }

    public function stats(): JsonResponse
    {
        $stats = Ticket::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $allStats = [
            'open' => $stats['open'] ?? 0,
            'in_progress' => $stats['in_progress'] ?? 0,
            'closed' => $stats['closed'] ?? 0,
        ];

        return response()->json($allStats);
    }
}

