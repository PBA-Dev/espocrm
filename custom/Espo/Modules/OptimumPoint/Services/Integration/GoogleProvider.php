<?php

namespace Espo\Modules\OptimumPoint\Services\Integration;

class GoogleProvider implements CalendarProviderInterface, ContactProviderInterface
{
    public function getProviderName(): string
    {
        return 'Google';
    }
}
