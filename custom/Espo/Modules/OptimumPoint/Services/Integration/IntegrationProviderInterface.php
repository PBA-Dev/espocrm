<?php

namespace Espo\Modules\OptimumPoint\Services\Integration;

use Espo\ORM\Entity;

interface IntegrationProviderInterface
{
    public function getProviderName(): string;

    public function getOAuthScopeList(): array;

    public function fetchRemoteAccountProfile(Entity $connection): array;

    public function fetchRemoteCalendarList(Entity $connection): array;
}
