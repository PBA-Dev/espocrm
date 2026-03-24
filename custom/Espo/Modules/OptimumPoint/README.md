# OptimumPoint Module

Custom EspoCRM extension module for the OptimumPoint CRM implementation.

## Phase 1 Areas

- reports
- workflows
- webhooks and API connectivity for n8n
- Google calendar and contact sync
- Outlook calendar and contact sync
- meeting scheduler
- brand customization

## Notes

- Keep implementation additive and upgrade-safe.
- Prefer supported metadata and client extension points over core edits.
- Do not finalize entity models for Phase 2 or Phase 3 inside this module until approved.
- Google and Outlook connections should reuse Espo's native `OAuthProvider` and `OAuthAccount` entities for credential and token storage.
- `OpIntegrationConnection` is the OptimumPoint orchestration record that ties ownership, sync settings, scheduler behavior, and provider account metadata together.
- Connection bootstrap and lifecycle actions live under OptimumPoint API routes so the module can prepare OAuth, connect, disconnect, and refresh provider metadata without exposing scheduler code to raw OAuth account details.
