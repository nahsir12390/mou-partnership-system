# NSUK Review Remediation Plan

## Scope and baseline

This plan applies to the existing Laravel 13, Livewire and Flux application. The baseline on 15 November 2026 contains 104 automated tests (101 passing, 3 skipped) and a Docker/Render presentation configuration. Existing uncommitted frontend dependency and header-navigation changes are preserved and included in this remediation work.

| ID | Priority | Existing implementation and hypothesis | Planned correction | Test strategy | Status |
|---|---|---|---|---|---|
| F01 | High | Demo accounts are seeded by the default seeder and Render starts the presentation with them. | Separate explicit demo mode from production; block predictable demo accounts in production and document administrator provisioning. | Seeder and authentication tests. | Implemented — production configuration must use `DEMO_MODE=false`. |
| F02 | High | `/up` health route and Docker health check exist; free Render services can cold-start. | Reduce startup work, retain opaque health endpoint, measure locally, document host limitations and recommended configuration. | Health route and startup configuration review. | In Progress |
| F03 | Medium | Days remaining is calculated independently in views with `diffInDays`. | Add a central agreement calendar-day method using Africa/Lagos and reuse it. | Frozen-date boundary tests. | Implemented |
| F04 | High | Activation requires a final signed MoU but sample active data lacks evidence. | Require recorded approval and final signed document for activation; mark seeded records as demonstration records. | Lifecycle tests. | Implemented |
| F05 | High | Global activity logs and workflow actions are separate sources. | Record workflow transition activity transactionally and expose reconciled record history. | Audit reconciliation tests. | Implemented |
| F06 | Medium | Queue labels and counts mix personal and institution-wide work. | Separate assigned, institution-wide and stage-specific totals; label next reviewer. | Queue visibility tests. | Implemented |
| F07 | High | NSUK theme uses several low-opacity green sidebar text values. | Introduce accessible color tokens and improve representative text/control contrast. | Manual contrast review and markup tests. | Not Started |
| F08 | High | Several raw form controls do not have labels. | Add IDs, labels, required/error semantics and accessible filter names. | Response markup tests. | Implemented |
| F09 | Medium | Required fields rely mainly on HTML `required`. | Add consistent visible required indicators and field-specific errors. | Validation response tests. | Implemented |
| F10 | Medium | Partner form hard-codes categories while registry derives stored categories. | Centralize category values and reuse them in validation, forms and filters. | Category filter tests. | Implemented |
| F11 | Medium | Review form renders a first decision option by default. | Add explicit empty choice, confirmation for consequential decisions and duplicate guard. | Review validation tests. | Implemented |
| F12 | Medium | Dashboard and alert obligation links open the agreement. | Add a record-specific obligation route and link to it. | Navigation and authorization tests. | Implemented |
| F13 | Medium | Agreement register uses a horizontally scrolling table at small widths. | Add a narrow-screen agreement card view. | Rendered markup/mobile review. | Implemented — card layout below the medium breakpoint. |
| F14 | Low | Summary metrics precede registry controls on mobile. | Make metrics compact on narrow screens. | Responsive markup/manual review. | Implemented — two-column compact metric cards. |
| F15 | Medium | Mobile drawer is Flux-managed and has not been keyboard verified. | Add Escape/backdrop/focus behavior if missing after component inspection. | Component-source and manual keyboard verification. | Implemented — Flux provides Escape dismissal, backdrop close, and focus restoration. |
| F16 | Medium | Reports and CSV exports are institution-wide and unfiltered. | Add common date, department, partner and status filters to metrics, lists and exports. | Scoped export tests. | Implemented |
| F17 | Low | Empty document state lacks a forward action. | Link authorized users to agreement selection before upload. | Empty-state markup test. | Implemented |
| F18 | Low | Some dates omit year and audit time omits timezone; grammar is inconsistent. | Centralize visible date/time presentation and pluralization. | Date and copy tests. | Implemented |

## Execution notes

- Schema changes will be additive only.
- Existing records will not be deleted or rewritten solely for the review.
- Presentation data is distinguished from institutional data in demo mode; it is not treated as evidence of a real approval.
- Deployment conclusions requiring Render account access or a paid plan will be documented rather than claimed as implemented.
