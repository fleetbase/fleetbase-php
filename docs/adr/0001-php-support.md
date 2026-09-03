# ADR 0001: PHP support policy

- Status: accepted for v1.1.0 implementation
- Date: 2026-08-31

## Context

Version 1.0.x declares `^7.4`, unintentionally excluding PHP 8. Existing consumers must not be abandoned during a backwards-compatible release, but PHP 7.4 and 8.0 no longer receive upstream security fixes.

## Decision

Version 1.1.0 will declare `php: ^7.4 || ^8.0` and test PHP 7.4 through 8.5. CI will resolve both the lowest and current compatible dependency sets. Documentation will distinguish compatibility from security support and recommend a PHP version still supported by php.net.

Development-only tools that cannot run on every supported runtime may use isolated Composer tool projects. Runtime source will remain syntactically valid on PHP 7.4.

## Consequences

Consumers gain PHP 8 compatibility without a major release. Supporting the old runtimes increases CI and dependency-resolution complexity. Raising the runtime floor is deferred to 2.0.0.
