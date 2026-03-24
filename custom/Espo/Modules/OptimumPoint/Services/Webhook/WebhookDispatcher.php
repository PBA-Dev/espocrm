<?php

namespace Espo\Modules\OptimumPoint\Services\Webhook;

use Espo\ORM\EntityManager;

class WebhookDispatcher
{
    public function __construct(
        private EntityManager $entityManager,
    ) {}

    public function dispatch(string $eventName, array $payload = []): array
    {
        $subscriptionList = $this->entityManager
            ->getRepository('OpWebhookSubscription')
            ->where([
                'isActive' => true,
                'eventName' => $eventName,
            ])
            ->find();

        $resultList = [];

        foreach ($subscriptionList as $entity) {
            $resultList[] = [
                'id' => $entity->getId(),
                'name' => $entity->get('name'),
                'eventName' => $eventName,
                'targetUrl' => $entity->get('targetUrl'),
                'status' => 'PendingSend',
                'payload' => $payload,
            ];
        }

        return $resultList;
    }
}
