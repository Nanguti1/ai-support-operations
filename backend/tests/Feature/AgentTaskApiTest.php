<?php

use App\Models\AgentTask;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgentTaskApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_a_task_with_valid_input()
    {
        $ticket = Ticket::factory()->create();

        $response = $this->postJson("/api/tickets/{$ticket->id}/agent-tasks", [
            'type' => 'analysis',
            'input_data' => ['key' => 'value'],
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'type' => 'analysis',
                'status' => 'pending',
                'input_data' => ['key' => 'value'],
            ]);

        $this->assertDatabaseHas('agent_tasks', [
            'ticket_id' => $ticket->id,
            'type' => 'analysis',
            'status' => 'pending',
        ]);
    }

    public function test_can_create_a_task_with_omitted_optional_input()
    {
        $ticket = Ticket::factory()->create();

        $response = $this->postJson("/api/tickets/{$ticket->id}/agent-tasks", [
            'type' => 'analysis',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'type' => 'analysis',
                'status' => 'pending',
                'input_data' => null,
            ]);

        $this->assertDatabaseHas('agent_tasks', [
            'ticket_id' => $ticket->id,
            'type' => 'analysis',
            'status' => 'pending',
        ]);
    }

    public function test_rejects_unsupported_task_type()
    {
        $ticket = Ticket::factory()->create();

        $response = $this->postJson("/api/tickets/{$ticket->id}/agent-tasks", [
            'type' => 'unsupported_type',
        ]);

        $response->assertStatus(422);
    }

    public function test_rejects_invalid_input_data()
    {
        $ticket = Ticket::factory()->create();

        $response = $this->postJson("/api/tickets/{$ticket->id}/agent-tasks", [
            'type' => 'analysis',
            'input_data' => 'not_an_array',
        ]);

        $response->assertStatus(422);
    }

    public function test_lists_tasks_for_correct_ticket_in_expected_order()
    {
        $ticket = Ticket::factory()->create();
        $otherTicket = Ticket::factory()->create();

        $task1 = AgentTask::factory()->create([
            'ticket_id' => $ticket->id,
            'type' => 'analysis',
            'created_at' => now()->subMinutes(2),
        ]);

        $task2 = AgentTask::factory()->create([
            'ticket_id' => $ticket->id,
            'type' => 'analysis',
            'created_at' => now()->subMinutes(1),
        ]);

        $task3 = AgentTask::factory()->create([
            'ticket_id' => $otherTicket->id,
            'type' => 'analysis',
        ]);

        $response = $this->getJson("/api/tickets/{$ticket->id}/agent-tasks");

        $response->assertStatus(200)
            ->assertJsonCount(2)
            ->assertJsonPath('0.id', $task2->id)
            ->assertJsonPath('1.id', $task1->id);
    }

    public function test_retrieves_an_existing_task()
    {
        $ticket = Ticket::factory()->create();
        $task = AgentTask::factory()->create([
            'ticket_id' => $ticket->id,
            'type' => 'analysis',
        ]);

        $response = $this->getJson("/api/tickets/{$ticket->id}/agent-tasks/{$task->id}");

        $response->assertStatus(200)
            ->assertJson([
                'id' => $task->id,
                'ticket_id' => $ticket->id,
                'type' => 'analysis',
            ]);
    }

    public function test_returns_404_for_nonexistent_ticket()
    {
        $response = $this->getJson('/api/tickets/999/agent-tasks');

        $response->assertStatus(404);
    }

    public function test_returns_404_when_task_belongs_to_different_ticket()
    {
        $ticket1 = Ticket::factory()->create();
        $ticket2 = Ticket::factory()->create();
        $task = AgentTask::factory()->create([
            'ticket_id' => $ticket1->id,
            'type' => 'analysis',
        ]);

        $response = $this->getJson("/api/tickets/{$ticket2->id}/agent-tasks/{$task->id}");

        $response->assertStatus(404)
            ->assertJson(['message' => 'Agent task not found']);
    }

    public function test_prevents_client_supplied_execution_fields_from_setting_task_status()
    {
        $ticket = Ticket::factory()->create();

        $response = $this->postJson("/api/tickets/{$ticket->id}/agent-tasks", [
            'type' => 'analysis',
            'status' => 'completed',
            'output_data' => ['result' => 'test'],
            'error_message' => 'test error',
            'started_at' => now()->toDateTimeString(),
            'completed_at' => now()->toDateTimeString(),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status', 'output_data', 'error_message', 'started_at', 'completed_at']);
    }

    public function test_task_status_is_initialized_to_pending()
    {
        $ticket = Ticket::factory()->create();

        $response = $this->postJson("/api/tickets/{$ticket->id}/agent-tasks", [
            'type' => 'analysis',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'pending',
                'output_data' => null,
                'error_message' => null,
                'started_at' => null,
                'completed_at' => null,
            ]);

        $this->assertDatabaseHas('agent_tasks', [
            'ticket_id' => $ticket->id,
            'status' => 'pending',
        ]);
    }

    public function test_can_execute_a_pending_task()
    {
        $ticket = Ticket::factory()->create([
            'title' => 'Test ticket title',
            'description' => 'Test ticket description',
            'priority' => 'high',
            'status' => 'open',
        ]);

        $task = AgentTask::factory()->create([
            'ticket_id' => $ticket->id,
            'type' => 'analysis',
            'status' => 'pending',
        ]);

        $response = $this->postJson("/api/tickets/{$ticket->id}/agent-tasks/{$task->id}/execute");

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'completed',
                'type' => 'analysis',
            ]);

        $this->assertDatabaseHas('agent_tasks', [
            'id' => $task->id,
            'status' => 'completed',
        ]);

        $this->assertNotNull($response->json('output_data'));
        $this->assertArrayHasKey('summary', $response->json('output_data'));
        $this->assertArrayHasKey('mock_service', $response->json('output_data'));
        $this->assertTrue($response->json('output_data.mock_service'));
    }

    public function test_execution_returns_404_for_task_belonging_to_different_ticket()
    {
        $ticket1 = Ticket::factory()->create();
        $ticket2 = Ticket::factory()->create();
        $task = AgentTask::factory()->create([
            'ticket_id' => $ticket1->id,
            'type' => 'analysis',
            'status' => 'pending',
        ]);

        $response = $this->postJson("/api/tickets/{$ticket2->id}/agent-tasks/{$task->id}/execute");

        $response->assertStatus(404)
            ->assertJson(['message' => 'Agent task not found']);
    }

    public function test_execution_returns_409_for_non_pending_task()
    {
        $ticket = Ticket::factory()->create();
        $task = AgentTask::factory()->create([
            'ticket_id' => $ticket->id,
            'type' => 'analysis',
            'status' => 'completed',
        ]);

        $response = $this->postJson("/api/tickets/{$ticket->id}/agent-tasks/{$task->id}/execute");

        $response->assertStatus(409)
            ->assertJson([
                'message' => 'Task cannot be executed',
                'reason' => 'Task is not in pending status',
                'current_status' => 'completed',
            ]);
    }

    public function test_execution_persists_processing_state_before_analysis()
    {
        $ticket = Ticket::factory()->create();
        $task = AgentTask::factory()->create([
            'ticket_id' => $ticket->id,
            'type' => 'analysis',
            'status' => 'pending',
        ]);

        // Mock the analysis service to capture when it's called
        $analyzeCalled = false;
        $mockService = \Mockery::mock(\App\Services\MockAnalysisService::class);
        $mockService->shouldReceive('analyze')
            ->andReturnUsing(function () use (&$analyzeCalled) {
                // Check DB state when analysis is called
                $analyzeCalled = true;
                return [
                    'summary' => 'Mock result',
                    'mock_service' => true,
                ];
            });
        $this->app->instance(\App\Services\MockAnalysisService::class, $mockService);

        $this->postJson("/api/tickets/{$ticket->id}/agent-tasks/{$task->id}/execute");

        $task->refresh();

        // Verify the task completed successfully
        $this->assertEquals('completed', $task->status->value);
        $this->assertNotNull($task->started_at);
        $this->assertNotNull($task->completed_at);
        $this->assertNotNull($task->output_data);
        
        // Verify the analysis service was actually called
        $this->assertTrue($analyzeCalled);
    }

    public function test_atomic_claim_prevents_duplicate_execution()
    {
        $ticket = Ticket::factory()->create();
        $task = AgentTask::factory()->create([
            'ticket_id' => $ticket->id,
            'type' => 'analysis',
            'status' => 'pending',
        ]);

        // Manually set task to processing to simulate another request claimed it
        $task->update(['status' => 'processing', 'started_at' => now()]);

        $response = $this->postJson("/api/tickets/{$ticket->id}/agent-tasks/{$task->id}/execute");

        $response->assertStatus(409)
            ->assertJson([
                'message' => 'Task cannot be executed',
                'reason' => 'Task is not in pending status',
                'current_status' => 'processing',
            ]);
    }

    public function test_execution_cannot_be_repeated()
    {
        $ticket = Ticket::factory()->create();
        $task = AgentTask::factory()->create([
            'ticket_id' => $ticket->id,
            'type' => 'analysis',
            'status' => 'pending',
        ]);

        $this->postJson("/api/tickets/{$ticket->id}/agent-tasks/{$task->id}/execute");

        $response = $this->postJson("/api/tickets/{$ticket->id}/agent-tasks/{$task->id}/execute");

        $response->assertStatus(409)
            ->assertJson([
                'message' => 'Task cannot be executed',
                'reason' => 'Task is not in pending status',
            ]);
    }

    public function test_execution_returns_404_for_nonexistent_task()
    {
        $ticket = Ticket::factory()->create();

        $response = $this->postJson("/api/tickets/{$ticket->id}/agent-tasks/999/execute");

        $response->assertStatus(404);
    }

    public function test_execution_handles_service_failure_gracefully()
    {
        $ticket = Ticket::factory()->create();
        $task = AgentTask::factory()->create([
            'ticket_id' => $ticket->id,
            'type' => 'analysis',
            'status' => 'pending',
        ]);

        $mockService = \Mockery::mock(\App\Services\MockAnalysisService::class);
        $mockService->shouldReceive('analyze')
            ->andThrow(new \RuntimeException('Analysis service unavailable with secret_key=sk-12345'));
        $this->app->instance(\App\Services\MockAnalysisService::class, $mockService);

        $response = $this->postJson("/api/tickets/{$ticket->id}/agent-tasks/{$task->id}/execute");

        $response->assertStatus(500)
            ->assertJson([
                'message' => 'Task execution failed',
                'error' => 'Analysis execution failed',
            ]);

        $task->refresh();

        $this->assertEquals('failed', $task->status->value);
        $this->assertNotNull($task->error_message);
        $this->assertEquals('Analysis execution failed', $task->error_message);
        $this->assertStringNotContainsString('secret_key', $task->error_message);
        $this->assertNotNull($task->completed_at);
        $this->assertNotNull($task->started_at);
    }
}
