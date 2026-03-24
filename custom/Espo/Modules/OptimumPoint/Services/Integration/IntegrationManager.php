<?php

namespace Espo\Modules\OptimumPoint\Services\Integration;

use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\Error;
use Espo\Core\Exceptions\NotFound;
use Espo\Core\InjectableFactory;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;

class IntegrationManager
{
    public function __construct(
        private EntityManager $entityManager,
        private InjectableFactory $injectableFactory
    ) {}

    /**
     * @throws NotFound
     */
    public function getConnectionById(string $id): Entity
    {
        $connection = $this->entityManager->getEntityById('OpIntegrationConnection', $id);

        if (!$connection) {
            throw new NotFound("Integration connection not found.");
        }

        return $connection;
    }

    /**
     * @throws BadRequest
     */
    public function getProviderForConnection(Entity $connection): IntegrationProviderInterface
    {
        $provider = $connection->get('provider');

        if (!is_string($provider) || $provider === '') {
            throw new BadRequest("Integration connection provider is not set.");
        }

        return match ($provider) {
            'Google' => $this->injectableFactory->create(GoogleProvider::class),
            'Outlook' => $this->injectableFactory->create(OutlookProvider::class),
            default => throw new BadRequest("Unsupported integration provider '$provider'."),
        };
    }

    /**
     * @throws BadRequest
     * @throws Error
     */
    public function getCalendarProviderForConnection(Entity $connection): CalendarProviderInterface
    {
        $provider = $this->getProviderForConnection($connection);

        if (!$provider instanceof CalendarProviderInterface) {
            throw new Error("Provider '{$provider->getProviderName()}' does not support calendar operations.");
        }

        return $provider;
    }

    /**
     * @throws BadRequest
     * @throws Error
     */
    public function getContactProviderForConnection(Entity $connection): ContactProviderInterface
    {
        $provider = $this->getProviderForConnection($connection);

        if (!$provider instanceof ContactProviderInterface) {
            throw new Error("Provider '{$provider->getProviderName()}' does not support contact operations.");
        }

        return $provider;
    }

    /**
     * @throws BadRequest
     */
    public function getLinkedOAuthProvider(Entity $connection): ?Entity
    {
        $oauthProviderId = $connection->get('oauthProviderId');

        if (!$oauthProviderId) {
            return null;
        }

        $oauthProvider = $this->entityManager->getEntityById('OAuthProvider', $oauthProviderId);

        if (!$oauthProvider) {
            throw new BadRequest("Linked OAuth provider does not exist.");
        }

        return $oauthProvider;
    }

    /**
     * @throws BadRequest
     */
    public function getLinkedOAuthAccount(Entity $connection): ?Entity
    {
        $oauthAccountId = $connection->get('oauthAccountId');

        if (!$oauthAccountId) {
            return null;
        }

        $oauthAccount = $this->entityManager->getEntityById('OAuthAccount', $oauthAccountId);

        if (!$oauthAccount) {
            throw new BadRequest("Linked OAuth account does not exist.");
        }

        return $oauthAccount;
    }

    /**
     * @throws BadRequest
     */
    public function validateProviderConfiguration(Entity $connection): void
    {
        $provider = $connection->get('provider');
        $oauthProvider = $this->getLinkedOAuthProvider($connection);

        if (!$oauthProvider) {
            return;
        }

        $oauthProviderName = $oauthProvider->get('name');

        if (!is_string($oauthProviderName) || $oauthProviderName === '') {
            throw new BadRequest("Linked OAuth provider has no name.");
        }

        if (!str_contains(strtolower($oauthProviderName), strtolower((string) $provider))) {
            throw new BadRequest("OAuth provider does not match the selected integration provider.");
        }
    }
}
