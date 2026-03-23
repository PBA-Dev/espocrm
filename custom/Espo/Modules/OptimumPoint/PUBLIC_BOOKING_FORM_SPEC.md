# OptimumPoint Public Booking Form Spec

## Phase 1 Baseline

This spec applies to the public client-facing booking form.

### Required Fields

- `firstName`
- `lastName`
- `emailAddress`
- `postalCode`
- `serviceCategory`
- `serviceType`
- `generalConsentAccepted`

### Optional Fields

- `phoneNumber`
- `notes`
- `timezone`

## Field Rules

### Name Fields

- Use separate `firstName` and `lastName` fields.
- Both are required.

### Email

- Required.
- Used for CRM matching before booking record creation.
- If a matching Contact or Lead is found by email, reuse it.
- If no match exists, create a new Lead.

### Postal Code

- Required for all public bookings.
- Stored for intake and routing context.

### Profession and Service Type

- `serviceCategory` is required.
- `serviceType` is required.
- `serviceType` options depend on the selected `serviceCategory`.
- Both option lists are managed centrally by CRM users.

### Consent

- One required general consent checkbox for Phase 1.
- The text should be broad enough to cover core privacy and contact-processing consent requirements.
- Profession-specific consent variants can be added later.

## Deferred for Later Slices

- profession-specific required fields
- profession-specific consent variants
- agent-side advanced intake form
- any customer-facing self-service area beyond the public booking form
- deeper field branching by service type
- HIPAA-specific handling if a profession or workflow requires it

## Confirmation Boundary

- Public users submit booking requests through the public booking form only.
- If a request enters a paused confirmation state, confirmation is staff-only inside the CRM.
- Customers should not receive CRM access as part of the Phase 1 booking flow.
