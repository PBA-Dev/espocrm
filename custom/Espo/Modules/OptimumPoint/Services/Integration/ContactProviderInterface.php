<?php

namespace Espo\Modules\OptimumPoint\Services\Integration;

use Espo\ORM\Entity;

interface ContactProviderInterface extends IntegrationProviderInterface
{
    public function importContacts(Entity $connection, array $options = []): array;

    public function exportContacts(Entity $connection, array $contactList, array $options = []): array;
}
