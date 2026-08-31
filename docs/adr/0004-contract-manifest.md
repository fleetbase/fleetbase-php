# ADR 0004: Generated endpoint inventory, reviewed SDK methods

- Status: accepted for v1.1.0 implementation
- Date: 2026-08-31

## Context

The Fleetbase and Core Postman collections define 220 in-scope requests. Hand-maintained endpoint lists drift, while mechanically generated public methods produce poor naming and can encode server quirks without review.

## Decision

`contracts/postman-manifest.json` is generated deterministically from the two locked Postman collections. Every row retains its source file, verb, URL, implementation, test references, mapping status, and any approved exception.

The manifest is the completeness gate, not a public-code generator. Human-reviewed service methods define the PHP API. Contract CI will fail on unmapped requests, duplicate identities, count drift, stale source refs, missing tests, or unapproved exceptions.

Storefront, Ledger, and Integrated Vendor Flow collections are excluded from v1.1.0 because they represent separate product contracts. Their support requires a separately approved scope.

## Consequences

The exact 220-request scope is auditable and reproducible. Public method design remains deliberate, at the cost of implementing and reviewing each mapping.
