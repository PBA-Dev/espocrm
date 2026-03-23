<?php

namespace Espo\Modules\OptimumPoint\Services\Scheduling;

class ConflictDetector
{
    /**
     * Phase 1 conflict policy:
     * - Ignore provider events that are clearly marked free or tentative when supported.
     * - For busy conflicts, return the conflicting event details.
     * - The booking flow must require explicit user confirmation before allowing an overlap.
     */
    public function detect(string $schedulerId, string $start, string $end): array
    {
        return [
            'schedulerId' => $schedulerId,
            'start' => $start,
            'end' => $end,
            'overlapPolicy' => 'WarnAndRequireOverride',
            'requiresOverride' => true,
            'conflictList' => [],
        ];
    }
}
