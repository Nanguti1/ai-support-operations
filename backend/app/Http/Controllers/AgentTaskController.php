<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAgentTaskRequest;
use App\Models\AgentTask;
use App\Models\Ticket;
use App\Services\MockAnalysisService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class AgentTaskController extends Controller
{
    public function index(Ticket $ticket): JsonResponse
    {
        $tasks = $ticket->agentTasks()
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        return response()->json($tasks);
    }

    public function store(StoreAgentTaskRequest $request, Ticket $ticket): JsonResponse
    {
        $task = $ticket->agentTasks()->create([
            'type' => $request->type,
            'input_data' => $request->input_data,
            'status' => 'pending',
            'output_data' => null,
            'error_message' => null,
            'started_at' => null,
            'completed_at' => null,
        ]);

        return response()->json($task, 201);
    }

    public function show(Ticket $ticket, AgentTask $agentTask): JsonResponse
    {
        if ($agentTask->ticket_id !== $ticket->id) {
            return response()->json(['message' => 'Agent task not found'], 404);
        }

        return response()->json($agentTask);
    }

    public function execute(Ticket $ticket, AgentTask $agentTask, MockAnalysisService $analysisService): JsonResponse
    {
        if ($agentTask->ticket_id !== $ticket->id) {
            return response()->json(['message' => 'Agent task not found'], 404);
        }

        // Atomically claim the task: only transition from pending to processing
        $affected = $agentTask->where('id', $agentTask->id)
            ->where('status', 'pending')
            ->update([
                'status' => 'processing',
                'started_at' => now(),
            ]);

        if ($affected === 0) {
            return response()->json([
                'message' => 'Task cannot be executed',
                'reason' => 'Task is not in pending status',
                'current_status' => $agentTask->fresh()->status->value,
            ], 409);
        }

        // Refresh to get the latest persisted state after claiming
        $agentTask->refresh();

        try {
            $result = $analysisService->analyze($ticket, $agentTask->input_data ?? []);

            $agentTask->update([
                'status' => 'completed',
                'output_data' => $result,
                'completed_at' => now(),
            ]);

            return response()->json($agentTask);
        } catch (\Exception $e) {
            // Log detailed exception server-side
            Log::error('Agent task execution failed', [
                'task_id' => $agentTask->id,
                'ticket_id' => $ticket->id,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Attempt to persist failure state
            try {
                $agentTask->update([
                    'status' => 'failed',
                    'error_message' => 'Analysis execution failed',
                    'completed_at' => now(),
                ]);
            } catch (\Exception $updateException) {
                // Log secondary failure without exposing details
                Log::error('Failed to persist agent task failure state', [
                    'task_id' => $agentTask->id,
                    'exception' => $updateException->getMessage(),
                ]);
            }

            return response()->json([
                'message' => 'Task execution failed',
                'error' => 'Analysis execution failed',
            ], 500);
        }
    }
}
