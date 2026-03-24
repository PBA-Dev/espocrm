# OptimumPoint Phase 1 Blueprint

## Confirmed Decisions

- Module name: `OptimumPoint`
- Brand name: `OptimumPoint`
- Integration email for initial provider setup: `alex.torrescanety@optimum-pflegeberatung.de`
- Google Phase 1 scope:
  - calendar sync
  - contact import into CRM
  - contact sync from CRM to Google
- Outlook Phase 1 scope:
  - calendar sync
  - contact import into CRM
  - contact sync from CRM to Outlook

## Initial Build Slices

1. Brand layer
   - register `OptimumPoint` theme
   - wire logo and favicon assets once files exist in the workspace
   - tune colors and dashboard defaults

2. Integration foundation
   - provider connection entity with user-owned and team-owned records
   - OAuth configuration flow
   - sync status tracking
   - webhook ingress and egress

3. Workflow foundation
   - trigger definitions
   - condition model
   - action model
   - outbound webhook subscriptions for OptimumPoint events
   - booking request creation triggers workflow and webhook evaluation

4. Reports foundation
   - saved report definition
   - report runner service
   - dashboard presentation

5. Meeting scheduler
   - availability rules
   - weekly hours and exceptions are scheduler-specific
   - booking workflow
   - booking requests are stored as first-class records for intake, review, and troubleshooting
   - public route can accept booking submissions and create paused booking requests
   - public route can return the service catalog for profession/service dropdowns
   - provider calendar write-back
   - user-owned and team-owned schedulers
   - optional round-robin assignment, off by default
   - optional customer choice of team member, off by default
   - plain calendar events by default, with connected provider promotion later
   - match by email first and otherwise create a new Lead
   - user and provider calendars must still be checked to prevent overlap
   - public booking auto-detects browser timezone and allows manual override
   - busy conflicts show the overlapping event and require explicit override approval
   - free or tentative provider events may be ignored when that status is exposed clearly
   - public booking form stays minimal for Phase 1
   - public booking includes a profession dropdown and a dependent service-type dropdown
   - profession and service type options are managed centrally by CRM users
   - required public form fields are first name, last name, email, postal code, profession, service type, and one general consent checkbox
   - phone stays optional in the base public form
   - profession-specific extra fields are deferred to a later slice
   - valid bookings can auto-create, but overlap or processing uncertainty should place the request in a paused confirmation state
   - paused confirmations are staff-only inside CRM, not customer-facing

## Current Constraint

The Phase 1 theme now points to `client/img/op_logo.png`. Favicon and any alternate login/header variants can be adjusted once final asset variants are selected.
