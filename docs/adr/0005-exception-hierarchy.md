# ADR 0005: Exception compatibility and diagnostics

- Status: accepted for v1.1.0 implementation
- Date: 2026-08-31

## Context

Version 1.0.x throws generic exceptions based only on an `error` JSON property. Consumers need typed failures and response context, but existing code may catch `FleetbaseException` or PHP's base `Exception`.

## Decision

All SDK failures will extend `Fleetbase\Sdk\FleetbaseException`. Specialized exceptions will cover authentication, authorization, validation, not found, conflict, rate limiting, server response, transport, timeout, decoding, and unexpected responses.

Exceptions may retain status, Fleetbase code, validation details, request ID, method, sanitized URL, raw response metadata, and previous exceptions. Messages and serialization must never expose authorization headers, API keys, or sensitive request bodies.

Legacy catch behavior remains valid because the existing base class name is retained and continues to extend `Exception`.

## Consequences

Consumers can handle precise failures while broad legacy catches continue to work. Every mapper branch and sanitization path requires deterministic tests.
