<?php

namespace App\Models;

use App\Enums\AgentTaskStatus;
use App\Enums\AgentTaskType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['type', 'input_data', 'status', 'output_data', 'error_message', 'started_at', 'completed_at'])]
class AgentTask extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status' => AgentTaskStatus::class,
            'type' => AgentTaskType::class,
            'input_data' => 'array',
            'output_data' => 'array',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }
}
