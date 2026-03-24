# Coding and Security Guidelines

This project follows the rules below for every implementation.

## Core Maxims

1. Do not break existing business flow.
2. Prevent 500 errors and dead links.
3. Keep admin/control features limited to authorized users only.
4. Optimize for mobile-first operation where possible.
5. Prefer additive, backward-compatible changes.

## Coding Standards

- Keep changes small, scoped, and reversible.
- Use clear naming and avoid hidden side effects.
- Do not duplicate logic; reuse existing services/helpers.
- Validate all request input in controllers/form requests.
- Keep view logic simple; move non-trivial logic to controller/service/model.
- Add comments only for non-obvious behavior.

## Security Requirements

- Enforce authentication and authorization on every admin route.
- Use permission middleware for sensitive actions (approve, assign, generate, delete).
- Never trust client input; validate and sanitize.
- Use CSRF protection on all state-changing forms/routes.
- Avoid exposing internal file paths or stack traces to users.
- Never commit secrets, credentials, or token values.
- Restrict one-time maintenance scripts with strong token and delete after use.

## Database and Migration Safety

- New columns should be nullable by default unless strictly required.
- Avoid destructive schema changes in normal feature rollout.
- Add indexes/uniqueness only where business identity requires it.
- Keep old records readable after migration.
- Provide safe fallback behavior if optional tables/columns are missing.

## Reliability and Error Handling

- Wrap risky filesystem/external operations in try/catch.
- Return user-safe error messages and log technical details server-side.
- Do not assume optional relations exist; always null-check.
- Keep download paths deterministic and sanitized.

## Mobile Usability Rules

- Primary actions must be visible near top on phone.
- Tables must support horizontal scroll (`overflow-x-auto`).
- Avoid truncating critical controls (Actions/Edit/Delete/Download).
- Keep tap targets reasonably large (`min-h-[40px]` or more where practical).

## Pre-Deployment Checklist (Required)

1. Route protection check
   - Confirm admin pages are under auth + role middleware.
   - Confirm sensitive endpoints have permission checks.
2. Validation check
   - New input fields validated and constrained.
3. Lint/error check
   - Run lints on all changed files and fix issues.
4. Regression check
   - Verify key flows still work (orders, shipments, invoices, auth).
5. Mobile check
   - Confirm action visibility and horizontal table access on phone width.
6. Migration check
   - Confirm migration is additive and safe; provide run instructions.
7. Security check
   - Confirm no debug/setup scripts left exposed.

## Incident Policy

- If any unexpected change or high-risk conflict is detected, stop and investigate before proceeding.
- Favor temporary safe fallback over system failure.

