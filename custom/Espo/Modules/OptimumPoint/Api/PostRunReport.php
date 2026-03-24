<?php

namespace Espo\Modules\OptimumPoint\Api;

use Espo\Core\Acl;
use Espo\Core\Api\Action;
use Espo\Core\Api\Request;
use Espo\Core\Api\Response;
use Espo\Core\Api\ResponseComposer;
use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\Forbidden;
use Espo\Modules\OptimumPoint\Services\Reporting\ReportRunner;

class PostRunReport implements Action
{
    public function __construct(
        private ReportRunner $runner,
        private Acl $acl,
    ) {}

    public function process(Request $request): Response
    {
        if (!$this->acl->checkScope('OpReport')) {
            throw new Forbidden();
        }

        $id = $request->getRouteParam('id') ?? throw new BadRequest('No report ID.');

        $result = $this->runner->run($id);

        return ResponseComposer::json($result);
    }
}
