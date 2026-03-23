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
   - webhook action for n8n

4. Reports foundation
   - saved report definition
   - report runner service
   - dashboard presentation

5. Meeting scheduler
   - availability rules
   - weekly hours and exceptions are scheduler-specific
   - booking workflow
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

## Current Constraint

The Phase 1 theme now points to `client/img/op_logo.png`. Favicon and any alternate login/header variants can be adjusted once final asset variants are selected.
