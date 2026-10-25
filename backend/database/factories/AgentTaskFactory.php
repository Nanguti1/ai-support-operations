<?php

namespace Database\Factories;

use App\Models\AgentTask;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AgentTask>
 */
class AgentTaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ticket_id' => Ticket::factory(),
            'type' => 'analysis',
            'status' => fake()->randomElement(['pending', 'processing', 'completed', 'failed']),
            'input_data' => fake()->optional()->randomElement([['key' => 'value'], null]),
            'output_data' => fake()->optional()->randomElement([['result' => 'test'], null]),
            'error_message' => fake()->optional()->sentence(),
            'started_at' => fake()->optional()->dateTime(),
            'completed_at' => fake()->optional()->dateTime(),
        ];
    }
}
