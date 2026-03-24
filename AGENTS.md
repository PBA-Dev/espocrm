# AGENTS.md

## Project Context

This repository is a full EspoCRM `9.3.3` source checkout.

Custom work is being implemented as an installable module under:

- `custom/Espo/Modules/OptimumPoint`

Brand/module name:

- `OptimumPoint`

Primary brand logo currently used by the custom theme:

- `client/img/op_logo.png`

PHP CLI used for validation in this environment:

- `C:\php-8.5.4\php.exe`

Note:

- `php` is not reliably on the current shell `PATH`, so use the full executable path for validation commands.

## High-Level Goal

Build a custom OptimumPoint extension for EspoCRM with:

- Phase 1:
  - Google Integration
  - Outlook Integration
  - Meeting Scheduler
  - Reports
  - Workflows
  - Webhooks/API support
- Phase 2:
  - BPM
  - Products/Pricing
  - Quotes/Invoices
  - Purchases
  - Subscriptions
  - Inventory
  - Payments
  - Zoom
  - VoIP
  - MailChimp
  - Stripe
- Phase 3:
  - Project Management
  - Projects
  - Tasks and Milestones
  - Kanban Boards
  - Gantt Chart

## Files Added/Used for Planning

- `IMPLEMENTATION_PLAN.md`
- `custom/Espo/Modules/OptimumPoint/PHASE1_BLUEPRINT.md`
- `custom/Espo/Modules/OptimumPoint/PUBLIC_BOOKING_FORM_SPEC.md`
- `custom/Espo/Modules/OptimumPoint/README.md`

These files contain the current implementation roadmap and decisions and should be updated as work progresses.

## Important Git Note

The base repo `.gitignore` ignored all of `custom/Espo/Modules/*`.

An exception was added for:

- `custom/Espo/Modules/OptimumPoint/**`

So the custom module is now trackable in Git.

## What Has Been Built So Far

### 1. Module Foundation

Created the basic module scaffold:

- `custom/Espo/Modules/OptimumPoint/Resources/module.json`
- module docs and planning files
- service namespaces under `Services/*`
- controllers for new custom entities

### 2. Brand Foundation

Created custom theme metadata:

- `custom/Espo/Modules/OptimumPoint/Resources/metadata/themes/OptimumPoint.json`
- `custom/Espo/Modules/OptimumPoint/Resources/i18n/en_US/Global.json`

Theme currently points to:

- `client/img/op_logo.png`

Theme is still basic and mostly metadata-level. Full CSS/login/navbar polish is not done yet.

### 3. Integration Foundation

Added provider abstraction stubs:

- `Services/Integration/CalendarProviderInterface.php`
- `Services/Integration/ContactProviderInterface.php`
- `Services/Integration/GoogleProvider.php`
- `Services/Integration/OutlookProvider.php`

Added integration connection entity:

- `OpIntegrationConnection`

Files include:

- `Resources/metadata/scopes/OpIntegrationConnection.json`
- `Resources/metadata/entityDefs/OpIntegrationConnection.json`
- `Resources/metadata/clientDefs/OpIntegrationConnection.json`
- `Resources/layouts/OpIntegrationConnection/*`
- `Resources/i18n/en_US/OpIntegrationConnection.json`
- `Controllers/OpIntegrationConnection.php`

Current intent:

- support both user-owned and team-owned Google/Outlook connections
- allow calendar sync plus contact import/export in Phase 1

### 4. Meeting Scheduler Foundation

Added scheduler entity:

- `OpMeetingScheduler`

Files include:

- `Resources/metadata/scopes/OpMeetingScheduler.json`
- `Resources/metadata/entityDefs/OpMeetingScheduler.json`
- `Resources/metadata/clientDefs/OpMeetingScheduler.json`
- `Resources/layouts/OpMeetingScheduler/*`
- `Resources/i18n/en_US/OpMeetingScheduler.json`
- `Controllers/OpMeetingScheduler.php`

Current scheduler decisions already encoded:

- user-owned and team-owned schedulers
- round-robin available but off by default
- customer choice of team member available but off by default
- default calendar mode starts as plain calendar event
- connected provider can later be promoted as default
- booking match mode is match-by-email, else create lead
- timezone mode is browser auto-detect plus manual override
- overlap policy is warning-based and requires explicit override
- paused confirmations are staff-only inside CRM

### 5. Scheduler Availability Foundation

Added weekly rules entity:

- `OpSchedulerAvailabilityRule`

Added exception entity:

- `OpSchedulerAvailabilityException`

Files include metadata, layouts, i18n, and controllers for both.

Current rules:

- weekly hours and exceptions are scheduler-specific
- user/provider calendars must still be checked to prevent overlap

Related service stubs:

- `Services/Scheduling/AvailabilityResolver.php`
- `Services/Scheduling/ConflictDetector.php`
- `Services/Scheduling/BookingIntakeService.php`

### 6. Service Taxonomy Foundation

Added public-booking taxonomy entities:

- `OpServiceCategory` for profession
- `OpServiceType` for dependent service list

Files include metadata, layouts, i18n, and controllers for both.

Intent:

- CRM users manage profession list centrally
- CRM users manage service type list centrally
- booking form uses profession first, then filtered service type

### 7. Booking Request Foundation

Added booking request entity:

- `OpBookingRequest`

Files include:

- `Resources/metadata/scopes/OpBookingRequest.json`
- `Resources/metadata/entityDefs/OpBookingRequest.json`
- `Resources/metadata/clientDefs/OpBookingRequest.json`
- `Resources/layouts/OpBookingRequest/*`
- `Resources/i18n/en_US/OpBookingRequest.json`
- `Controllers/OpBookingRequest.php`

This captures:

- scheduler
- profession/service type
- first name / last name / email / postal code
- phone / notes / timezone
- requested start/end
- general consent
- overlap override acceptance
- requires confirmation
- match result
- linked matched lead/contact
- linked created meeting
- conflict summary

### 8. Public Booking API Endpoints

Custom module routes were added in:

- `custom/Espo/Modules/OptimumPoint/Resources/routes.json`

Current custom routes:

- `POST /api/v1/OptimumPoint/BookingRequest`
- `GET /api/v1/OptimumPoint/ServiceCatalog`

Action classes:

- `Api/PostPublicBookingRequest.php`
- `Api/GetServiceCatalog.php`

Current behavior:

- booking submit validates required fields
- booking submit persists a real `OpBookingRequest`
- request is created in `Paused` state
- request has `requiresConfirmation = true`
- service catalog endpoint returns active profession/service options

### 9. Workflow / Webhook Foundation

Added workflow rule entity:

- `OpWorkflowRule`

Added outbound webhook subscription entity:

- `OpWebhookSubscription`

Files include metadata, layouts, i18n, and controllers for both.

Updated services:

- `Services/Workflow/WorkflowEngine.php`
- `Services/Webhook/WebhookDispatcher.php`
- `Services/Scheduling/BookingRequestProcessor.php`

Current behavior:

- when a booking request is created, workflow rules for `OptimumPoint.BookingRequest.Created` are evaluated
- outbound webhook subscriptions for `OptimumPoint.BookingRequest.Created` are evaluated
- current implementation returns matched rule/subscription lists; it does not yet perform real task creation, notification creation, or HTTP webhook sending

### 10. Reports Foundation

Added report entity:

- `OpReport`

Files include:

- `Resources/metadata/scopes/OpReport.json`
- `Resources/metadata/entityDefs/OpReport.json`
- `Resources/metadata/clientDefs/OpReport.json`
- `Resources/layouts/OpReport/*`
- `Resources/i18n/en_US/OpReport.json`
- `Controllers/OpReport.php`

Added authenticated run endpoint:

- route: `POST /api/v1/OptimumPoint/Report/:id/run`
- action: `Api/PostRunReport.php`

Updated report service:

- `Services/Reporting/ReportRunner.php`

Current report capabilities:

- save report definitions as records
- target any entity type
- support report types:
  - `List`
  - `Count`
  - `GroupedCount`
- support simple Phase 1 filters with:
  - `equals`
  - `notEquals`
  - `in`
  - `notIn`
  - `greaterThan`
  - `lessThan`
  - `greaterOrEquals`
  - `lessOrEquals`
- list reports return selected fields
- count reports return a metric
- grouped-count reports return grouped key/count rows

Still not done for reports:

- dashboard dashlet UI using report definitions
- chart-specific frontend components
- advanced filter builder UI
- ACL-aware strict-access query path review
- export/download endpoints

## Public Booking Form Decisions Already Confirmed

These are important and should not be re-guessed in a new conversation.

### Required Public Fields

- first name
- last name
- email
- postal code
- profession
- service type
- one general consent checkbox

### Optional Public Fields

- phone
- notes
- timezone

### Matching Rule

- match by email first
- if no CRM match exists, create a new Lead

### Confirmation Rule

- if overlap or processing uncertainty exists, booking is paused
- paused confirmations are staff-only inside CRM
- customers do not get CRM access as part of Phase 1

### Timezone Rule

- auto-detect browser timezone
- allow manual override

### Overlap Rule

- if provider exposes free/tentative clearly, those can be ignored
- busy conflicts should show the conflicting event
- overlap requires explicit approval

## What Is Still Stubbed / Not Done

Phase 1 is not complete.

Still missing or only stubbed:

- full meeting scheduler end-to-end logic
- actual lead/contact matching implementation
- actual meeting creation
- actual conflict detection against real calendars
- actual Google integration
- actual Outlook integration
- actual report engine and report entities
- actual workflow execution actions
- actual HTTP webhook sending for OptimumPoint subscriptions
- report dashboard/dashlet integration
- advanced report UI and exports
- UI for public booking page
- admin polish for theme/login/navbar/dashboard
- rebuild/runtime verification inside a running Espo instance

## Current Priority Order

User asked to focus on these first:

1. Meeting Scheduler
2. Workflow / Webhooks
3. Reports
4. Google / Outlook integrations

Progress against that list:

- Meeting Scheduler: partial foundation + public backend endpoints
- Workflow / Webhooks: foundation in place
- Reports: foundation in place
- Google / Outlook integrations: not started

## Native Espo API Status

This EspoCRM instance already has a usable native API.

Confirmed from core:

- API routes in `application/Espo/Resources/routes.json`
- OpenAPI endpoint via `/OpenApi`
- built-in API key generation route
- built-in webhook infrastructure

Implication:

- no need to build a core CRM API from scratch
- only OptimumPoint-specific endpoints and business actions need to be added

## Validation Performed

Multiple custom PHP files were validated using:

- `C:\php-8.5.4\php.exe -l <file>`

Syntax checks passed for the custom controllers, API actions, and service files created so far.

## Recommended Next Step After Handoff

Continue with the remaining Phase 1 priorities in this order:

1. Finish Meeting Scheduler backend logic
   - real match/create lead logic
   - real conflict evaluation
   - real meeting creation
   - provider-aware scheduler behavior
2. Build Reports foundation
3. Build actual Google integration
4. Build actual Outlook integration

If a new conversation is started, the new agent should read:

- `AGENTS.md`
- `IMPLEMENTATION_PLAN.md`
- `custom/Espo/Modules/OptimumPoint/PHASE1_BLUEPRINT.md`
- `custom/Espo/Modules/OptimumPoint/PUBLIC_BOOKING_FORM_SPEC.md`

before making new architectural decisions.
