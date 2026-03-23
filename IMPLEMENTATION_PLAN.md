# EspoCRM Extension Implementation Plan

## Goal

Build a custom installable EspoCRM extension for this instance with:

- Phase 1:
  - Google Integration
  - Outlook Integration
  - Meeting Scheduler
  - Reports
  - Workflows
  - Webhooks and API support for n8n connectivity
- Phase 2:
  - Business Process Management
  - Products and Pricing
  - Quotation and Invoicing
  - Purchases
  - Subscriptions
  - Inventory Management
  - Payments
  - Zoom Integration
  - VoIP Integration
  - MailChimp Integration
  - Stripe Integration
- Phase 3:
  - Project Management
  - Projects
  - Tasks and Milestones
  - Kanban Boards
  - Gantt Chart

## Delivery Principles

- Keep all custom logic inside `custom/Espo/Modules/<ModuleName>`.
- Avoid modifying core files unless there is no supported extension point.
- Separate brand customization from business logic where possible.
- Build installable, versioned extension packages.
- Do not proceed on unresolved naming, data model, or integration-auth assumptions.

## High-Level Architecture

The extension should be structured into these internal areas:

1. Platform
   - shared module metadata
   - settings entities
   - integration auth storage
   - webhook dispatch and intake
   - shared service interfaces
   - audit logging

2. Automation
   - workflow triggers
   - condition evaluation
   - action execution
   - scheduled automation jobs

3. Reporting
   - report definitions
   - query service
   - dashlets
   - exports

4. Scheduling
   - availability model
   - booking flow
   - meeting creation
   - provider sync adapters

5. Integrations
   - Google provider
   - Outlook provider
   - n8n-oriented webhook/API helpers

6. Branding
   - theme metadata
   - labels
   - logo and favicon assets
   - login and navbar customization
   - dashboard defaults

## Phase 1 Build Order

### Step 1: Module Foundation

- Confirm module name, package name, and brand name.
- Create module folder structure under `custom/Espo/Modules/<ModuleName>`.
- Add:
  - `Resources/module.json`
  - `Resources/metadata`
  - `Resources/i18n/en_US`
  - `Controllers`
  - `Services`
  - `Hooks`
  - `Entities`
  - `Jobs`
  - `client/src`
  - `client/res`

### Step 2: Branding Foundation

- Create a custom theme entry.
- Add branded labels.
- Add placeholder assets for:
  - navbar logo
  - login logo
  - favicon
- Decide which UI areas remain Espo defaults and which get branded overrides.

### Step 3: Integration Foundation

- Create integration settings storage and admin configuration surfaces.
- Add outbound webhook dispatcher.
- Add inbound authenticated webhook endpoints.
- Define stable payload contracts for n8n.
- Add integration event logs and failure visibility.

### Step 4: Workflow Engine v1

- Add trigger registry:
  - record created
  - record updated
  - field changed
  - scheduled/date trigger
- Add action registry:
  - update record
  - create record
  - create task
  - send notification
  - send webhook
- Add condition evaluation.
- Add execution logging.

### Step 5: Reports v1

- Add report definition entity.
- Implement report query service.
- Add list/chart-style outputs.
- Add dashlets for home dashboards.
- Add CSV export.

### Step 6: Meeting Scheduler

- Add availability configuration.
- Add booking endpoint and page flow.
- Add meeting creation logic.
- Add conflict detection.
- Add webhook emission for downstream automations.

### Step 7: Google and Outlook

- Implement OAuth-backed provider adapters.
- Start with calendar sync and scheduler write-back.
- Add contact sync only after core calendar behavior is stable.
- Keep mail sync out of the first cut unless it becomes a must-have.

## Phase 2 Preparation Notes

These features depend on a stable data model and should not start until a domain model is approved:

- products
- price books
- quotes
- invoices
- purchases
- subscriptions
- inventory
- payments

Business Process Management should be designed on top of the Phase 1 workflow foundation rather than built as a separate unrelated engine.

## Immediate Unknowns That Block Scaffolding

The following must be confirmed before code scaffolding starts:

- module name
- brand name
- public-facing product name for the extension package
- whether Google and Outlook scope is calendar-only for Phase 1 or also includes contacts
- whether Meeting Scheduler is public booking only or also internal round-robin/team scheduling

## Next Implementation Move

Once naming is confirmed:

1. scaffold the module
2. add the Phase 1 folder structure
3. create the base integration and branding metadata
4. document file-by-file build tasks for the first implementation slice
