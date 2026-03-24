<?php

namespace Espo\Modules\OptimumPoint\Services\Integration;

use Espo\ORM\Entity;

interface CalendarProviderInterface extends IntegrationProviderInterface
{
    public function fetchBusyRanges(Entity $connection, string $start, string $end): array;

    public function createCalendarEvent(Entity $connection, array $payload): array;

    public function updateCalendarEvent(Entity $connection, string $remoteEventId, array $payload): array;
}
