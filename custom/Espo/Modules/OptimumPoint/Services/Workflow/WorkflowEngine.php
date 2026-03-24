<?php

namespace Espo\Modules\OptimumPoint\Services\Workflow;

use Espo\ORM\EntityManager;

class WorkflowEngine
{
    public function __construct(
        private EntityManager $entityManager,
    ) {}

    public function run(string $triggerName, array $context = []): array
    {
        $ruleList = $this->entityManager
            ->getRepository('OpWorkflowRule')
            ->where([
                'isActive' => true,
                'triggerEvent' => $triggerName,
            ])
            ->find();

        $resultList = [];

        foreach ($ruleList as $entity) {
            $resultList[] = [
                'id' => $entity->getId(),
                'name' => $entity->get('name'),
                'triggerEvent' => $triggerName,
                'actionType' => $entity->get('actionType'),
                'targetEntityType' => $entity->get('targetEntityType'),
                'assignedUserId' => $entity->get('assignedUserId'),
                'context' => $context,
            ];
        }

        return $resultList;
    }
}
