<?php

namespace Espo\Modules\OptimumPoint\Services\Scheduling;

class BookingIntakeService
{
    /**
     * Phase 1 booking rule:
     * - Match by email first.
     * - If no match exists, create a new Lead.
     */
    public function resolveParticipantByEmail(string $emailAddress): array
    {
        return [
            'bookingMatchMode' => 'MatchOrCreateLead',
            'emailAddress' => $emailAddress,
        ];
    }
}
