<?php

namespace Espo\Modules\OptimumPoint\Api;

use Espo\Core\Api\Action;
use Espo\Core\Api\Request;
use Espo\Core\Api\Response;
use Espo\Core\Api\ResponseComposer;
use Espo\ORM\EntityManager;

class GetServiceCatalog implements Action
{
    public function __construct(
        private EntityManager $entityManager,
    ) {}

    public function process(Request $request): Response
    {
        $categoryList = $this->entityManager
            ->getRepository('OpServiceCategory')
            ->where(['isActive' => true])
            ->order('sortOrder', 'asc')
            ->find();

        $typeList = $this->entityManager
            ->getRepository('OpServiceType')
            ->where(['isActive' => true])
            ->order('sortOrder', 'asc')
            ->find();

        $result = [
            'serviceCategoryList' => [],
            'serviceTypeList' => [],
        ];

        foreach ($categoryList as $entity) {
            $result['serviceCategoryList'][] = [
                'id' => $entity->getId(),
                'name' => $entity->get('name'),
                'sortOrder' => $entity->get('sortOrder'),
            ];
        }

        foreach ($typeList as $entity) {
            $result['serviceTypeList'][] = [
                'id' => $entity->getId(),
                'name' => $entity->get('name'),
                'serviceCategoryId' => $entity->get('serviceCategoryId'),
                'sortOrder' => $entity->get('sortOrder'),
            ];
        }

        return ResponseComposer::json($result);
    }
}
