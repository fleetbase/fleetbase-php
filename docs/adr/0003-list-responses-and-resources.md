# ADR 0003: List responses and mutable resources

- Status: accepted for v1.1.0 implementation
- Date: 2026-08-31
- Corrected: 2026-09-02 after maintainer API-contract review

## Context

Version 1.0.x returns list responses as arrays and exposes mutable resource objects. Fleetbase API v1 does not define a pagination response contract. Replacing mutable resources in a minor release would break consumers.

## Decision

Resources remain mutable in 1.x. Their implementation gains correct dirty/change tracking, lifecycle state, lossless nested hydration, `toArray()`, `JsonSerializable`, and `ArrayAccess` where these additions do not alter legacy access.

List methods preserve their documented array behavior. The SDK may defensively hydrate recognized `data`, `results`, or `items` envelopes, but it does not expose pagination methods or invent client-side pagination without an API contract.

## Consequences

Existing resource and list workflows remain viable without implying unsupported server behavior. An immutable-only resource API, if desired, requires 2.0.0.
