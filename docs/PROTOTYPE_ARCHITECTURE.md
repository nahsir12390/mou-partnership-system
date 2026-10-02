# Institutional MoU & Partnership Tracking System

## Prototype Domain Architecture

This document records the working domain model for the prototype. It is intentionally subject to stakeholder review before production implementation.

## Core relationship map

```text
Department ──< Users >── Role
    │
    ├──< MoUs / Agreements >── Partner
    │          │
    │          ├──< Approval Steps
    │          ├──< Obligations ──< Milestones
    │          ├──< Documents
    │          ├──< Renewal Records
    │          └──< Activity / Audit Records
    │
    └──< Assigned Obligations
```

## Planned entities

### Partner
Represents an external institution or organization participating in one or more partnerships.

Planned fields: name, category, country, state/location, address, website, primary contact name/email/phone, status and notes.

### Agreement (MoU)
Central record for an MoU or related institutional agreement.

Planned fields: reference number, title, partner, owning department, responsible officer, agreement type, summary/purpose, start date, expiry date, status, approval stage, renewal status and notes.

Working lifecycle: Draft -> Submitted -> Under Review -> Approved -> Awaiting Signature -> Active -> Expired/Renewed/Closed. The exact workflow must be confirmed by stakeholders.

### Approval Step
Records review and approval activity for an agreement.

Planned fields: agreement, stage, assigned role/user, decision, comments, requested date and decision date.

### Obligation
Tracks a commitment or deliverable arising from an agreement.

Planned fields: agreement, title, description, responsible department/user, priority, due date, status and completion date.

### Milestone
Breaks an obligation or agreement deliverable into trackable checkpoints.

Planned fields: obligation, title, description, due date, status, completion date and evidence/notes.

### Document
Stores metadata for files associated with an agreement.

Planned fields: agreement, category, title, file path, version, uploaded by, upload date and description. Production design must account for version history and secure storage.

### Renewal Record
Tracks expiry decisions, extensions, amendments and renewal history.

Planned fields: agreement, previous expiry date, new expiry date, decision, decision date, notes and supporting document.

### Activity / Audit Record
Records important actions performed in the system.

Planned fields: actor, action, subject type/id, description, metadata, IP address and timestamp.

## Foundation entities already implemented

- Users
- Roles
- Departments

Initial prototype roles:

- System Administrator
- Management
- Legal / Review Officer
- Department Officer
- Read-only User

These role names are prototype assumptions and must remain configurable until stakeholder approval.

## Design rules

1. A Partner can have many agreements.
2. Each agreement belongs to a partner and an owning department.
3. An agreement can have many approval steps, obligations, documents, renewal records and audit records.
4. Obligations can be assigned to departments and/or responsible users.
5. Obligations can contain multiple milestones.
6. Workflow/status values should not be hard-wired into UI assumptions where stakeholder confirmation is still required.
7. Prototype data may be seeded for demonstrations; production data rules will be finalized after review.
8. Authorization will ultimately be enforced server-side, not only by hiding navigation items.

## Phase boundary

Phase 1 establishes the technical shell and domain architecture. Phase 2 will build the management dashboard using prototype data and the domain model above. Detailed production database constraints, final workflows, permissions and acceptance criteria remain subject to stakeholder confirmation.
