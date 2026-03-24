<?php

namespace Espo\Modules\OptimumPoint\Api;

use Espo\Core\Api\Action;
use Espo\Core\Api\Request;
use Espo\Core\Api\Response;
use Espo\Core\Api\ResponseComposer;
use Espo\Core\Exceptions\BadRequest;
use Espo\Modules\OptimumPoint\Services\Scheduling\BookingRequestProcessor;

class PostPublicBookingRequest implements Action
{
    public function __construct(
        private BookingRequestProcessor $processor,
    ) {}

    public function process(Request $request): Response
    {
        $data = $request->getParsedBody();

        if (!is_object($data) && !is_array($data)) {
            throw new BadRequest('No payload.');
        }

        $result = $this->processor->process((array) $data);

        return ResponseComposer::json($result);
    }
}
