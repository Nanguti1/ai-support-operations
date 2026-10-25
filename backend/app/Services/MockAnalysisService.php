<?php

namespace App\Services;

use App\Models\Ticket;

class MockAnalysisService
{
    /**
     * Perform a mock analysis on a ticket.
     * This is a deterministic simulation, not real AI.
     *
     * @param Ticket $ticket The ticket to analyze
     * @param array|null $inputData Optional input data from the task
     * @return array Structured mock analysis result
     */
    public function analyze(Ticket $ticket, ?array $inputData = null): array
    {
        // Simulate analysis based on ticket properties
        $wordCount = str_word_count($ticket->title . ' ' . ($ticket->description ?? ''));

        // Get string values from enums
        $priority = $ticket->priority->value;
        $status = $ticket->status->value;

        // Deterministic mock output based on ticket data
        return [
            'summary' => 'Mock analysis of ticket: ' . $ticket->title,
            'word_count' => $wordCount,
            'priority_assessment' => $this->mockPriorityAssessment($priority),
            'status_assessment' => $this->mockStatusAssessment($status),
            'recommendation' => $this->mockRecommendation($priority, $status),
            'analysis_timestamp' => now()->toIso8601String(),
            'mock_service' => true,
        ];
    }

    /**
     * Generate a mock priority assessment.
     */
    private function mockPriorityAssessment(string $priority): string
    {
        return match ($priority) {
            'high' => 'Ticket marked as high priority - consider immediate attention',
            'medium' => 'Ticket marked as medium priority - standard response time expected',
            'low' => 'Ticket marked as low priority - can be addressed during available capacity',
            default => 'Unknown priority level',
        };
    }

    /**
     * Generate a mock status assessment.
     */
    private function mockStatusAssessment(string $status): string
    {
        return match ($status) {
            'open' => 'Ticket is open and awaiting triage',
            'in_progress' => 'Ticket is currently being worked on',
            'closed' => 'Ticket has been resolved and closed',
            default => 'Unknown status',
        };
    }

    /**
     * Generate a mock recommendation based on priority and status.
     */
    private function mockRecommendation(string $priority, string $status): string
    {
        if ($status === 'closed') {
            return 'Ticket is closed - no action required unless reopened';
        }

        return match ($priority) {
            'high' => 'Assign to senior team member for immediate resolution',
            'medium' => 'Add to standard queue for normal processing',
            'low' => 'Address during maintenance windows or low-activity periods',
            default => 'Follow standard triage procedures',
        };
    }
}
