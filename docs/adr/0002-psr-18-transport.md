# ADR 0002: PSR-18 transport boundary

- Status: accepted for v1.1.0 implementation
- Date: 2026-08-31

## Context

The 1.0.x client constructs Guzzle directly, mixes request building with decoding, and cannot accept framework-managed HTTP clients or deterministic PSR test doubles.

## Decision

The internal transport boundary will use PSR-18 plus PSR-7 request and stream factories. Guzzle remains the default zero-configuration implementation. The public `Fleetbase\Sdk\HttpClient` class and its verb methods remain as compatibility adapters, while a public raw `request()` method provides forward compatibility.

Configuration, request construction, transport, response decoding, and error mapping will be separate components. Configuration will preserve self-hosted path prefixes and sanitize credentials from diagnostics.

## Consequences

Laravel, Symfony, HTTPlug-compatible, and custom PSR-18 clients can be injected without framework runtime dependencies. Guzzle-specific behavior is no longer allowed to leak through new APIs, although the legacy facade remains callable.
