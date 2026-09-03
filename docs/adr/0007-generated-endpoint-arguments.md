# ADR 0007: Generated endpoint argument conventions

## Status

Accepted for 1.1.2.

## Context

The 1.1.0 generated methods exposed the generator's transport envelope. A caller had to pass URL identifiers and API bodies inside arrays named `parameters` and then pass lower-level request options separately. That made otherwise ordinary calls such as changing a driver password look structurally different from `dispatchOrder($orderId)` and from the SDK's standard resource methods.

The generated surface has 220 requests. Their contract payloads are 97 JSON, 30 query, two raw JSON, one multipart, and 90 without a payload. Five URLs contain two identifiers; no URL contains more than two. PHP 8 named arguments make the published parameter names part of the compatibility contract.

## Decision

Public examples use this order:

1. URL identifiers, in URL order;
2. direct API body, query, or multipart data;
3. optional request/transport options.

For example, `changeDriverPassword($driverId, $data, $requestOptions)` and `capturePhotoForOrder($orderId, $subjectId, $data, $requestOptions)`.

Generated methods retain the published parameter names `parameters` and `options` and accept the 1.1.0 envelope whenever the first argument is an array on an endpoint with path identifiers. Runtime normalization is centralized in `Service`; generated traits contain no endpoint-specific overload logic. Ambiguous double specification is rejected. The implementation remains valid on PHP 7.4 and does not rely on union types or named-argument-only syntax.

Collection endpoints keep their existing two-array signature because their direct API data is already the first argument. Multipart data is a standard list of parts rather than an SDK-specific wrapper.

## Consequences

- Existing 1.1.0 positional and named calls remain source compatible.
- New examples match how callers think about URL identifiers and API data.
- HTTP transport keys remain an internal concern except when deliberately supplied as advanced request options.
- Generator metadata records path order and request-data placement, so tests, documentation, and the disposable live bridge use one source of truth.
- Every locked request is tested in both forms, and compatibility is checked against the authoritative 1.1.0 public API snapshot.
