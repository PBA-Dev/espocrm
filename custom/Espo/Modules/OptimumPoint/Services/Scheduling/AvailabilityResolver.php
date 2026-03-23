<?php

namespace Espo\Modules\OptimumPoint\Services\Scheduling;

class AvailabilityResolver
{
    /**
     * Phase 1 scheduling rule set:
     * - Scheduler-specific weekly hours define base bookable windows.
     * - Scheduler-specific exceptions add or block time.
     * - User and provider calendars must still be checked to prevent overlap.
     * - Public booking should auto-detect browser timezone and allow manual override.
     */
    public function resolve(string $schedulerId, string $start, string $end): array
    {
        return [
            'schedulerId' => $schedulerId,
            'start' => $start,
            'end' => $end,
            'mode' => 'SchedulerRulesWithCalendarConflictCheck',
            'timezoneMode' => 'BrowserWithManualOverride',
        ];
    }
}
