<?php

namespace Espo\Modules\OptimumPoint\Services\Integration;

use Espo\ORM\Entity;

class OutlookProvider implements CalendarProviderInterface, ContactProviderInterface
{
    public function getProviderName(): string
    {
        return 'Outlook';
    }

    public function getOAuthScopeList(): array
    {
        return [
            'openid',
            'email',
            'profile',
            'offline_access',
            'User.Read',
            'Calendars.ReadWrite',
            'Contacts.ReadWrite',
        ];
    }

    public function fetchRemoteAccountProfile(Entity $connection): array
    {
        return [
            'provider' => $this->getProviderName(),
            'connectionId' => $connection->getId(),
            'emailAddress' => $connection->get('emailAddress'),
            'status' => 'NotImplemented',
        ];
    }

    public function fetchRemoteCalendarList(Entity $connection): array
    {
        return [];
    }

    public function fetchBusyRanges(Entity $connection, string $start, string $end): array
    {
        return [];
    }

    public function createCalendarEvent(Entity $connection, array $payload): array
    {
        return [
            'provider' => $this->getProviderName(),
            'connectionId' => $connection->getId(),
            'payload' => $payload,
            'status' => 'NotImplemented',
        ];
    }

    public function updateCalendarEvent(Entity $connection, string $remoteEventId, array $payload): array
    {
        return [
            'provider' => $this->getProviderName(),
            'connectionId' => $connection->getId(),
            'remoteEventId' => $remoteEventId,
            'payload' => $payload,
            'status' => 'NotImplemented',
        ];
    }

    public function importContacts(Entity $connection, array $options = []): array
    {
        return [
            'provider' => $this->getProviderName(),
            'connectionId' => $connection->getId(),
            'options' => $options,
            'status' => 'NotImplemented',
        ];
    }

    public function exportContacts(Entity $connection, array $contactList, array $options = []): array
    {
        return [
            'provider' => $this->getProviderName(),
            'connectionId' => $connection->getId(),
            'contactCount' => count($contactList),
            'options' => $options,
            'status' => 'NotImplemented',
        ];
    }
}
