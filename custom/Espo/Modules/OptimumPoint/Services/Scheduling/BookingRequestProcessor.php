<?php

namespace Espo\Modules\OptimumPoint\Services\Scheduling;

use Espo\Core\Exceptions\BadRequest;
use Espo\ORM\EntityManager;
use Espo\Modules\OptimumPoint\Services\Webhook\WebhookDispatcher;
use Espo\Modules\OptimumPoint\Services\Workflow\WorkflowEngine;

class BookingRequestProcessor
{
    public function __construct(
        private EntityManager $entityManager,
        private WorkflowEngine $workflowEngine,
        private WebhookDispatcher $webhookDispatcher,
    ) {}

    /**
     * Phase 1 intake flow:
     * - persist the booking request,
     * - resolve contact/lead by email,
     * - evaluate scheduler availability and conflicts,
     * - create the meeting when permitted,
     * - pause for confirmation when overlap or processing uncertainty exists,
     * - staff complete confirmations inside the CRM only,
     * - write back match/conflict results.
     */
    public function process(array $payload): array
    {
        $requiredFieldList = [
            'meetingSchedulerId',
            'serviceCategoryId',
            'serviceTypeId',
            'firstName',
            'lastName',
            'emailAddress',
            'postalCode',
            'requestedStart',
            'requestedEnd',
        ];

        foreach ($requiredFieldList as $field) {
            if (empty($payload[$field])) {
                throw new BadRequest("No `$field`.");
            }
        }

        if (empty($payload['generalConsentAccepted'])) {
            throw new BadRequest('General consent must be accepted.');
        }

        $bookingRequest = $this->entityManager->getRepository('OpBookingRequest')->getNew();

        $bookingRequest->set('name', $this->generateReference());
        $bookingRequest->set('status', 'Paused');
        $bookingRequest->set('requiresConfirmation', true);
        $bookingRequest->set('meetingSchedulerId', $payload['meetingSchedulerId']);
        $bookingRequest->set('serviceCategoryId', $payload['serviceCategoryId']);
        $bookingRequest->set('serviceTypeId', $payload['serviceTypeId']);
        $bookingRequest->set('firstName', trim((string) $payload['firstName']));
        $bookingRequest->set('lastName', trim((string) $payload['lastName']));
        $bookingRequest->set('emailAddress', trim((string) $payload['emailAddress']));
        $bookingRequest->set('postalCode', trim((string) $payload['postalCode']));
        $bookingRequest->set('phoneNumber', $payload['phoneNumber'] ?? null);
        $bookingRequest->set('notes', $payload['notes'] ?? null);
        $bookingRequest->set('requestedStart', $payload['requestedStart']);
        $bookingRequest->set('requestedEnd', $payload['requestedEnd']);
        $bookingRequest->set('timezone', $payload['timezone'] ?? null);
        $bookingRequest->set('generalConsentAccepted', true);
        $bookingRequest->set('overlapOverrideAccepted', (bool) ($payload['overlapOverrideAccepted'] ?? false));
        $bookingRequest->set(
            'conflictSummary',
            'Pending scheduler conflict evaluation and staff confirmation.'
        );
        $bookingRequest->set('matchResult', 'NoMatchProcessed');

        $this->entityManager->saveEntity($bookingRequest);

        $context = [
            'bookingRequestId' => $bookingRequest->getId(),
            'status' => $bookingRequest->get('status'),
            'emailAddress' => $bookingRequest->get('emailAddress'),
            'meetingSchedulerId' => $bookingRequest->get('meetingSchedulerId'),
        ];

        $workflowResult = $this->workflowEngine->run('OptimumPoint.BookingRequest.Created', $context);
        $webhookResult = $this->webhookDispatcher->dispatch('OptimumPoint.BookingRequest.Created', $context);

        return [
            'id' => $bookingRequest->getId(),
            'reference' => $bookingRequest->get('name'),
            'status' => $bookingRequest->get('status'),
            'requiresConfirmation' => (bool) $bookingRequest->get('requiresConfirmation'),
            'workflowRuleCount' => count($workflowResult),
            'webhookSubscriptionCount' => count($webhookResult),
        ];
    }

    private function generateReference(): string
    {
        return 'OPBR-' . date('Ymd-His') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
    }
}
