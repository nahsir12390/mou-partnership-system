# NSUK Review Remediation Status

## Executive Summary

This document tracks remediation of the university technical review for the NSUK Institutional MoU & Partnership Management System. It was created after an initial repository and workflow inspection on 15 November 2026. Findings are only marked verified when automated or documented manual verification has been completed.

## Findings Status

| ID | Finding | Priority | Status | Implementation Summary | Verification |
|---|---|---|---|---|---|
| F01 | Demo administrator access | High | Implemented | Demo seeding and predictable credentials are limited to `DEMO_MODE=true`; optional MFA is enabled. | Authentication coverage passes; production must set `DEMO_MODE=false`. |
| F02 | Render wake-up screen | High | In Progress | Existing Docker health endpoint and startup sequence are under review. | Render plan behaviour requires manual host verification. |
| F03 | Remaining-day consistency | Medium | In Progress | Africa/Lagos calendar-day method and midnight boundary tests added. | Automated date tests pass; display consolidation remains. |
| F04 | Active evidence | High | Implemented | Activation requires both final signed MoU and approval action; seeded agreements are marked demo. | Lifecycle test coverage passes. |
| F05 | Audit reconciliation | High | Not Started | Activity logs and approval history use separate sources. | Pending. |
| F06 | Approval ownership | Medium | Not Started | Queue semantics require clearer separation. | Pending. |
| F07 | Contrast | High | Not Started | Theme requires contrast assessment. | Pending manual review. |
| F08 | Accessible names | High | Not Started | Raw controls require semantic label review. | Pending. |
| F09 | Required fields | Medium | Not Started | Required indicators are inconsistent. | Pending. |
| F10 | Partner categories | Medium | Implemented | Categories are configuration-backed, shared with forms and validated server-side. | Request validation and rendered form review. |
| F11 | Preselected decision | Medium | Not Started | Review form requires an explicit placeholder choice. | Pending. |
| F12 | Obligation navigation | Medium | Not Started | Links currently target the parent agreement. | Pending. |
| F13 | Mobile agreement register | Medium | Implemented | Narrow screens receive record cards; table remains desktop-only. | Responsive markup review. |
| F14 | Mobile summary cards | Low | Implemented | Summary uses a compact two-column grid. | Responsive markup review. |
| F15 | Drawer Escape support | Medium | Not Started | Requires Flux component keyboard verification. | Pending manual verification. |
| F16 | Reporting filters | Medium | Implemented | Department, partner, status and expiry-date filters now scope metrics, lists, and all CSV exports consistently. | Filtered page/export integration test passes. |
| F17 | Empty document action | Low | Implemented | Authorized users receive an agreement-selection action and upload guidance. | Feature suite passes. |
| F18 | Date, timezone and wording | Low | Not Started | Several views omit years/timezone and have copy inconsistencies. | Pending. |

## Testing Results

Current verification: 107 tests executed; 105 passed; 2 skipped; 273 assertions. `npm run build` passes. PHP static analysis still reports legacy type-annotation findings and requires a larger typing remediation; it is not used as a resolved-quality claim.

## Outstanding Deployment Considerations

- Free Render plans can suspend inactive services. This platform behaviour cannot be removed through application code.
- The presentation deployment uses an ephemeral SQLite database and demo seed data by design. A production deployment requires persistent database and document storage, a non-demo `APP_KEY`, individual administrator accounts, SMTP, backups and monitoring.
