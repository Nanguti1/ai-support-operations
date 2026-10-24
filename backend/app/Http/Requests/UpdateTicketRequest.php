<?php

namespace App\Http\Requests;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['prohibited'],
            'description' => ['prohibited'],
            'status' => ['nullable', Rule::enum(TicketStatus::class), 'required_without:priority'],
            'priority' => ['nullable', Rule::enum(TicketPriority::class), 'required_without:status'],
        ];
    }
}
