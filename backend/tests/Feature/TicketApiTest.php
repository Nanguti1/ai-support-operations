<?php

use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_a_ticket()
    {
        $response = $this->postJson('/api/tickets', [
            'title' => 'Test ticket',
            'description' => 'Test description',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'title' => 'Test ticket',
                'description' => 'Test description',
                'status' => 'open',
                'priority' => 'medium',
            ]);

        $this->assertDatabaseHas('tickets', [
            'title' => 'Test ticket',
            'status' => 'open',
            'priority' => 'medium',
        ]);
    }

    public function test_can_create_a_ticket_with_custom_status_and_priority()
    {
        $response = $this->postJson('/api/tickets', [
            'title' => 'Test ticket',
            'status' => 'in_progress',
            'priority' => 'high',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'title' => 'Test ticket',
                'status' => 'in_progress',
                'priority' => 'high',
            ]);

        $this->assertDatabaseHas('tickets', [
            'title' => 'Test ticket',
            'status' => 'in_progress',
            'priority' => 'high',
        ]);
    }

    public function test_cannot_create_a_ticket_with_invalid_status()
    {
        $response = $this->postJson('/api/tickets', [
            'title' => 'Test ticket',
            'status' => 'invalid_status',
        ]);

        $response->assertStatus(422);
    }

    public function test_cannot_create_a_ticket_without_title()
    {
        $response = $this->postJson('/api/tickets', [
            'description' => 'Test description',
        ]);

        $response->assertStatus(422);
    }

    public function test_can_list_tickets_with_pagination()
    {
        Ticket::factory()->count(15)->create();

        $response = $this->getJson('/api/tickets?page=1&limit=10');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'current_page',
                'per_page',
                'total',
                'last_page',
            ])
            ->assertJsonPath('current_page', 1)
            ->assertJsonPath('per_page', 10)
            ->assertJsonPath('total', 15)
            ->assertJsonPath('last_page', 2);

        $this->assertCount(10, $response->json('data'));
    }

    public function test_can_filter_tickets_by_status()
    {
        Ticket::factory()->create(['status' => 'open']);
        Ticket::factory()->create(['status' => 'in_progress']);
        Ticket::factory()->create(['status' => 'closed']);

        $response = $this->getJson('/api/tickets?status=open');

        $response->assertStatus(200)
            ->assertJsonPath('total', 1);

        $this->assertEquals('open', $response->json('data.0.status'));
    }

    public function test_pagination_respects_limit_cap()
    {
        Ticket::factory()->count(150)->create();

        $response = $this->getJson('/api/tickets?limit=200');

        $response->assertStatus(200)
            ->assertJsonPath('per_page', 100);

        $this->assertCount(100, $response->json('data'));
    }

    public function test_can_get_ticket_statistics()
    {
        Ticket::factory()->count(5)->create(['status' => 'open']);
        Ticket::factory()->count(3)->create(['status' => 'in_progress']);
        Ticket::factory()->count(2)->create(['status' => 'closed']);

        $response = $this->getJson('/api/tickets/stats');

        $response->assertStatus(200)
            ->assertJson([
                'open' => 5,
                'in_progress' => 3,
                'closed' => 2,
            ]);
    }

    public function test_stats_includes_zero_count_statuses()
    {
        Ticket::factory()->count(3)->create(['status' => 'open']);

        $response = $this->getJson('/api/tickets/stats');

        $response->assertStatus(200)
            ->assertJson([
                'open' => 3,
                'in_progress' => 0,
                'closed' => 0,
            ]);
    }

    public function test_can_update_ticket_status()
    {
        $ticket = Ticket::factory()->create(['status' => 'open']);

        $response = $this->patchJson("/api/tickets/{$ticket->id}", [
            'status' => 'in_progress',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'id' => $ticket->id,
                'status' => 'in_progress',
            ]);

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'status' => 'in_progress',
        ]);
    }

    public function test_can_update_ticket_priority()
    {
        $ticket = Ticket::factory()->create(['priority' => 'low']);

        $response = $this->patchJson("/api/tickets/{$ticket->id}", [
            'priority' => 'high',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'id' => $ticket->id,
                'priority' => 'high',
            ]);

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'priority' => 'high',
        ]);
    }

    public function test_can_update_both_status_and_priority()
    {
        $ticket = Ticket::factory()->create([
            'status' => 'open',
            'priority' => 'low',
        ]);

        $response = $this->patchJson("/api/tickets/{$ticket->id}", [
            'status' => 'closed',
            'priority' => 'high',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'id' => $ticket->id,
                'status' => 'closed',
                'priority' => 'high',
            ]);
    }

    public function test_cannot_update_ticket_with_invalid_status()
    {
        $ticket = Ticket::factory()->create();

        $response = $this->patchJson("/api/tickets/{$ticket->id}", [
            'status' => 'invalid_status',
        ]);

        $response->assertStatus(422);
    }

    public function test_cannot_update_ticket_without_providing_status_or_priority()
    {
        $ticket = Ticket::factory()->create();

        $response = $this->patchJson("/api/tickets/{$ticket->id}", []);

        $response->assertStatus(422);
    }

    public function test_cannot_update_ticket_title_or_description()
    {
        $ticket = Ticket::factory()->create(['title' => 'Original title']);

        $response = $this->patchJson("/api/tickets/{$ticket->id}", [
            'title' => 'New title',
        ]);

        $response->assertStatus(422);
    }

    public function test_returns_404_when_updating_non_existent_ticket()
    {
        $response = $this->patchJson('/api/tickets/999', [
            'status' => 'closed',
        ]);

        $response->assertStatus(404)
            ->assertJson(['message' => 'Ticket not found']);
    }

    public function test_cannot_list_tickets_with_invalid_status()
    {
        $response = $this->getJson('/api/tickets?status=invalid');

        $response->assertStatus(422);
    }

    public function test_cannot_list_tickets_with_invalid_page()
    {
        $response = $this->getJson('/api/tickets?page=0');

        $response->assertStatus(422);
    }

    public function test_cannot_list_tickets_with_invalid_limit()
    {
        $response = $this->getJson('/api/tickets?limit=0');

        $response->assertStatus(422);
    }
}
