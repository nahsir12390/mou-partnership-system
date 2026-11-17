# NSUK Review Remediation Status

## Executive Summary

This document tracks remediation of the university technical review for the NSUK Institutional MoU & Partnership Management System. It was created after an initial repository and workflow inspection on 15 November 2026. Findings are only marked verified when automated or documented manual verification has been completed.

## Findings Status

| ID | Finding | Priority | Status | Implementation Summary | Verification |
|---|---|---|---|---|---|
| F01 | Demo administrator access | High | Implemented | Demo seeding and predictable credentials are limited to `DEMO_MODE=true`; optional MFA is enabled. | Authentication coverage passes; production must set `DEMO_MODE=false`. |
| F02 | Render wake-up screen | High | In Progress | Existing Docker health endpoint and startup sequence are under review. | Render plan behaviour requires manual host verification. |
| F03 | Remaining-day consistency | Medium | Implemented | Africa/Lagos calendar-day method is used throughout agreement displays, with midnight boundary coverage. | Date-boundary tests pass. |
| F04 | Active evidence | High | Implemented | Activation requires both final signed MoU and approval action; seeded agreements are marked demo. | Lifecycle test coverage passes. |
| F05 | Audit reconciliation | High | Implemented | Workflow actions create activity-log events and the agreement detail screen exposes both recent record events and the filtered full audit trail. | Workflow/audit integration test passes. |
| F06 | Approval ownership | Medium | Implemented | Administrator oversight is explicitly labelled institution-wide; stage and next reviewer are clear. | Queue rendering and workflow tests pass. |
| F07 | Contrast | High | Not Started | Theme requires contrast assessment. | Pending manual review. |
| F08 | Accessible names | High | Implemented | Raw controls now have associated labels or accessible names across agreement, obligation, renewal, user, report, document, audit, and approval workflows. | Markup scan confirms no remaining raw controls without IDs; feature suite passes. |
| F09 | Required fields | Medium | Implemented | Required fields are visibly identified and key forms provide field-level validation feedback. | Feature suite passes. |
| F10 | Partner categories | Medium | Implemented | Categories are configuration-backed, shared with forms and validated server-side. | Request validation and rendered form review. |
| F11 | Preselected decision | Medium | Implemented | Review UI requires deliberate decision selection, asks for confirmation, and disables repeat submission; server-side stage locking prevents race duplicates. | Approval workflow tests pass. |
| F12 | Obligation navigation | Medium | Implemented | Dashboard, alerts, and reports open the authorized obligation detail record. | Route and feature checks pass. |
| F13 | Mobile agreement register | Medium | Implemented | Narrow screens receive record cards; table remains desktop-only. | Responsive markup review. |
| F14 | Mobile summary cards | Low | Implemented | Summary uses a compact two-column grid. | Responsive markup review. |
| F15 | Drawer Escape support | Medium | Implemented | The installed Flux sidebar/popover implementation handles Escape, backdrop dismissal, and focus restoration. | Component source inspection; final browser smoke check remains recommended. |
| F16 | Reporting filters | Medium | Implemented | Department, partner, status and expiry-date filters now scope metrics, lists, and all CSV exports consistently. | Filtered page/export integration test passes. |
| F17 | Empty document action | Low | Implemented | Authorized users receive an agreement-selection action and upload guidance. | Feature suite passes. |
| F18 | Date, timezone and wording | Low | Implemented | Visible dates include years where operationally needed; audit timestamps explicitly state WAT. | View and automated checks pass. |

## Testing Results

Current verification: 110 tests executed; 108 passed; 2 skipped; 287 assertions. `npm run build` passes and the production dependency audit reports zero vulnerabilities. PHP static analysis still reports legacy type-annotation findings and requires a larger typing remediation; it is not used as a resolved-quality claim.

## Outstanding Deployment Considerations

- Free Render plans can suspend inactive services. This platform behaviour cannot be removed through application code.
- The presentation deployment uses an ephemeral SQLite database and demo seed data by design. A production deployment requires persistent database and document storage, a non-demo `APP_KEY`, individual administrator accounts, SMTP, backups and monitoring.
