<?php

namespace Espo\Modules\OptimumPoint\Api;

use Espo\Core\Acl;
use Espo\Core\Api\Action;
use Espo\Core\Api\Request;
use Espo\Core\Api\Response;
use Espo\Core\Api\ResponseComposer;
use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\Forbidden;
use Espo\Modules\OptimumPoint\Services\Integration\IntegrationConnectionService;
use Espo\Modules\OptimumPoint\Services\Integration\IntegrationManager;

class PostRefreshIntegrationConnection implements Action
{
    public function __construct(
        private IntegrationManager $integrationManager,
        private IntegrationConnectionService $service,
        private Acl $acl,
    ) {}

    public function process(Request $request): Response
    {
        $id = $request->getRouteParam('id') ?? throw new BadRequest('No integration connection ID.');
        $connection = $this->integrationManager->getConnectionById($id);

        if (!$this->acl->checkEntityEdit($connection)) {
            throw new Forbidden();
        }

        return ResponseComposer::json($this->service->refresh($id));
    }
}
