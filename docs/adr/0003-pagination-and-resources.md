# ADR 0003: Pagination and mutable resources

- Status: accepted for v1.1.0 implementation
- Date: 2026-08-31

## Context

Version 1.0.x assumes list responses are bare arrays and exposes mutable resource objects. Current API responses may use envelopes and pagination metadata. Replacing mutable resources in a minor release would break consumers.

## Decision

Resources remain mutable in 1.x. Their implementation will gain correct dirty/change tracking, lifecycle state, lossless nested hydration, `toArray()`, `JsonSerializable`, and `ArrayAccess` where these additions do not alter legacy access.

Paginated responses will use an iterable collection value object containing items, links, and metadata. Legacy list methods will preserve their documented array behavior; explicit pagination methods will expose the collection object.

## Consequences

Existing resource workflows remain viable while new code can consume pagination safely. An immutable-only resource API, if desired, requires 2.0.0.
