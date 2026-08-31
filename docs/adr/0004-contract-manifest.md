# ADR 0004: Generated endpoint inventory, reviewed SDK methods

- Status: accepted for v1.1.0 implementation
- Date: 2026-08-31

## Context

The Fleetbase and Core Postman collections define 220 in-scope requests. Hand-maintained endpoint lists drift, while mechanically generated public methods produce poor naming and can encode server quirks without review.

## Decision

`contracts/postman-manifest.json` is generated deterministically from the two locked Postman collections. Every row retains its source file, verb, URL, implementation, test references, mapping status, and any approved exception.

The manifest is the completeness gate, not runtime magic. A deterministic scaffold writes explicit, checked-in service methods so the locked contract can be reproduced, but those names and signatures are reviewed as public API before commit. Contract CI fails on unmapped requests, duplicate identities, count drift, stale source refs, missing implementations, missing tests, or unapproved exceptions.

Storefront, Ledger, and Integrated Vendor Flow collections are excluded from v1.1.0 because they represent separate product contracts. Their support requires a separately approved scope.

## Consequences

The exact 220-request scope is auditable and reproducible. Public methods remain visible to reflection and static analysis, and generator output is treated as reviewable source rather than proof of correctness by itself.
