<?php

namespace Espo\Modules\OptimumPoint\Services\Integration;

class OutlookProvider implements CalendarProviderInterface, ContactProviderInterface
{
    public function getProviderName(): string
    {
        return 'Outlook';
    }
}
