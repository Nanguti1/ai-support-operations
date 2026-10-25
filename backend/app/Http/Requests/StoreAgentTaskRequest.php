<?php

namespace App\Http\Requests;

use App\Enums\AgentTaskType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAgentTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::enum(AgentTaskType::class)],
            'input_data' => ['nullable', 'array'],
            'status' => ['prohibited'],
            'output_data' => ['prohibited'],
            'error_message' => ['prohibited'],
            'started_at' => ['prohibited'],
            'completed_at' => ['prohibited'],
        ];
    }
}
