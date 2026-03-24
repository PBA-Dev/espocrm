<?php

namespace Espo\Modules\OptimumPoint\Services\Integration;

use DateTimeImmutable;
use DateTimeZone;
use Espo\Core\Di;
use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\Forbidden;
use Espo\Entities\OAuthAccount;
use Espo\Entities\OAuthProvider;
use Espo\Tools\OAuth\ConfigDataProvider;
use Espo\Tools\OAuth\ConnectionService as OAuthConnectionService;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;

class IntegrationConnectionService implements Di\UserAware
{
    use Di\UserSetter;

    public function __construct(
        private IntegrationManager $integrationManager,
        private EntityManager $entityManager,
        private ConfigDataProvider $oAuthConfigDataProvider,
        private OAuthConnectionService $oAuthConnectionService,
    ) {}

    /**
     * @throws BadRequest
     * @throws Forbidden
     */
    public function prepareOAuth(string $connectionId): array
    {
        $connection = $this->integrationManager->getConnectionById($connectionId);

        $this->integrationManager->validateProviderConfiguration($connection);

        $oauthProvider = $this->requireOAuthProvider($connection);
        $oauthAccount = $this->ensureOAuthAccount($connection, $oauthProvider);

        return [
            'connectionId' => $connection->getId(),
            'oauthProviderId' => $oauthProvider->getId(),
            'oauthAccountId' => $oauthAccount->getId(),
            'provider' => $connection->get('provider'),
            'isConnected' => $oauthAccount->get('accessToken') !== null,
            'authorizationData' => $this->buildAuthorizationData($oauthProvider),
        ];
    }

    /**
     * @throws BadRequest
     * @throws Forbidden
     */
    public function connect(string $connectionId, string $code): array
    {
        $connection = $this->integrationManager->getConnectionById($connectionId);

        $this->integrationManager->validateProviderConfiguration($connection);

        $oauthProvider = $this->requireOAuthProvider($connection);
        $oauthAccount = $this->ensureOAuthAccount($connection, $oauthProvider);

        $this->oAuthConnectionService->connect($oauthAccount, $code);

        return $this->refresh($connectionId);
    }

    /**
     * @throws BadRequest
     * @throws Forbidden
     */
    public function disconnect(string $connectionId): array
    {
        $connection = $this->integrationManager->getConnectionById($connectionId);
        $oauthAccount = $this->requireOAuthAccount($connection);

        $this->guardOAuthAccountAccess($oauthAccount);
        $this->oAuthConnectionService->disconnect($oauthAccount);

        $connection->set([
            'status' => 'Draft',
            'lastError' => null,
            'lastSyncAt' => null,
            'lastCalendarSyncAt' => null,
            'lastContactImportAt' => null,
            'lastContactExportAt' => null,
            'remoteAccountId' => null,
            'remoteCalendarId' => null,
            'remoteCalendarName' => null,
            'syncCursor' => null,
        ]);

        $this->entityManager->saveEntity($connection);

        return [
            'connectionId' => $connection->getId(),
            'oauthAccountId' => $oauthAccount->getId(),
            'status' => $connection->get('status'),
            'isConnected' => false,
        ];
    }

    /**
     * @throws BadRequest
     * @throws Forbidden
     */
    public function refresh(string $connectionId): array
    {
        $connection = $this->integrationManager->getConnectionById($connectionId);
        $oauthAccount = $this->requireOAuthAccount($connection);

        $this->guardOAuthAccountAccess($oauthAccount);

        if ($oauthAccount->get('accessToken') === null) {
            throw new BadRequest('OAuth account is not connected.');
        }

        $provider = $this->integrationManager->getProviderForConnection($connection);
        $profile = $provider->fetchRemoteAccountProfile($connection);
        $calendarList = $provider->fetchRemoteCalendarList($connection);
        $primaryCalendar = $this->extractPrimaryCalendar($calendarList);
        $now = $this->getNowString();

        $updateData = [
            'status' => 'Active',
            'lastError' => null,
            'lastSyncAt' => $now,
        ];

        if ($connection->get('calendarSyncEnabled')) {
            $updateData['lastCalendarSyncAt'] = $now;
        }

        if (array_key_exists('emailAddress', $profile) && is_string($profile['emailAddress'])) {
            $updateData['emailAddress'] = $profile['emailAddress'];
        }

        if (array_key_exists('remoteAccountId', $profile) && is_string($profile['remoteAccountId'])) {
            $updateData['remoteAccountId'] = $profile['remoteAccountId'];
        }

        if (array_key_exists('syncCursor', $profile) && is_string($profile['syncCursor'])) {
            $updateData['syncCursor'] = $profile['syncCursor'];
        }

        if ($primaryCalendar) {
            if (isset($primaryCalendar['id']) && is_string($primaryCalendar['id'])) {
                $updateData['remoteCalendarId'] = $primaryCalendar['id'];
            }

            if (isset($primaryCalendar['name']) && is_string($primaryCalendar['name'])) {
                $updateData['remoteCalendarName'] = $primaryCalendar['name'];
            }
        }

        $connection->set($updateData);

        $this->entityManager->saveEntity($connection);

        return [
            'connectionId' => $connection->getId(),
            'oauthAccountId' => $oauthAccount->getId(),
            'status' => $connection->get('status'),
            'isConnected' => true,
            'profile' => $profile,
            'calendarList' => $calendarList,
        ];
    }

    /**
     * @throws BadRequest
     */
    private function requireOAuthProvider(Entity $connection): OAuthProvider
    {
        $oauthProvider = $this->integrationManager->getLinkedOAuthProvider($connection);

        if (!$oauthProvider instanceof OAuthProvider) {
            throw new BadRequest('Integration connection is missing an OAuth provider.');
        }

        return $oauthProvider;
    }

    /**
     * @throws BadRequest
     * @throws Forbidden
     */
    private function ensureOAuthAccount(Entity $connection, OAuthProvider $oauthProvider): OAuthAccount
    {
        $existing = $this->integrationManager->getLinkedOAuthAccount($connection);

        if ($existing instanceof OAuthAccount) {
            $this->guardOAuthAccountAccess($existing);

            if ($existing->get('providerId') !== $oauthProvider->getId()) {
                throw new BadRequest('Linked OAuth account does not belong to the selected OAuth provider.');
            }

            return $existing;
        }

        $oauthAccount = $this->entityManager->getNewEntity(OAuthAccount::ENTITY_TYPE);
        $oauthAccount->set([
            'name' => sprintf('%s %s Connection', (string) $connection->get('provider'), (string) $connection->get('name')),
            'providerId' => $oauthProvider->getId(),
            'userId' => $this->user->getId(),
            'description' => sprintf(
                'OptimumPoint integration connection %s (%s).',
                (string) $connection->get('name'),
                (string) $connection->getId()
            ),
        ]);

        $this->entityManager->saveEntity($oauthAccount);

        $connection->set([
            'oauthAccountId' => $oauthAccount->getId(),
            'status' => 'Draft',
        ]);

        $this->entityManager->saveEntity($connection);

        return $oauthAccount;
    }

    /**
     * @throws BadRequest
     */
    private function requireOAuthAccount(Entity $connection): OAuthAccount
    {
        $oauthAccount = $this->integrationManager->getLinkedOAuthAccount($connection);

        if (!$oauthAccount instanceof OAuthAccount) {
            throw new BadRequest('Integration connection is not linked to an OAuth account.');
        }

        return $oauthAccount;
    }

    /**
     * @throws Forbidden
     */
    private function guardOAuthAccountAccess(OAuthAccount $oauthAccount): void
    {
        $oauthAccountUserId = $oauthAccount->get('userId');

        if ($oauthAccountUserId && $oauthAccountUserId !== $this->user->getId() && !$this->user->isAdmin()) {
            throw new Forbidden('OAuth account belongs to another user.');
        }
    }

    private function buildAuthorizationData(OAuthProvider $oauthProvider): array
    {
        $scope = null;

        if ($oauthProvider->getScopes()) {
            $scope = implode($oauthProvider->getScopeSeparator() ?? ' ', $oauthProvider->getScopes());
        }

        return [
            'endpoint' => $oauthProvider->getAuthorizationEndpoint(),
            'clientId' => $oauthProvider->getClientId(),
            'redirectUri' => $this->oAuthConfigDataProvider->getRedirectUri(),
            'scope' => $scope,
            'prompt' => $oauthProvider->getAuthorizationPrompt(),
            'params' => $oauthProvider->getAuthorizationParams(),
        ];
    }

    private function extractPrimaryCalendar(array $calendarList): ?array
    {
        foreach ($calendarList as $calendar) {
            if (
                is_array($calendar) &&
                !empty($calendar['isPrimary'])
            ) {
                return $calendar;
            }
        }

        foreach ($calendarList as $calendar) {
            if (is_array($calendar)) {
                return $calendar;
            }
        }

        return null;
    }

    private function getNowString(): string
    {
        return (new DateTimeImmutable('now', new DateTimeZone('UTC')))->format('Y-m-d H:i:s');
    }
}
