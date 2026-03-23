<?php

namespace Espo\Modules\OptimumPoint\Services\Scheduling;

class BookingRequestProcessor
{
    /**
     * Phase 1 intake flow:
     * - persist the booking request,
     * - resolve contact/lead by email,
     * - evaluate scheduler availability and conflicts,
     * - create the meeting when permitted,
     * - pause for confirmation when overlap or processing uncertainty exists,
     * - staff complete confirmations inside the CRM only,
     * - write back match/conflict results.
     */
    public function process(array $payload): array
    {
        return [
            'status' => 'Paused',
            'requiresConfirmation' => true,
            'payload' => $payload,
        ];
    }
}
