# fleetbase/fleetbase-php Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).


## [Unreleased]

## [1.1.0] - 2026-08-31

### Added

- PHP 8.0 through 8.5 compatibility and PSR-18/PSR-7 transport injection.
- Typed SDK exception hierarchy, defensive list-response hydration, deterministic contract/API snapshots, hermetic tests, and modern repository guidance.

### Changed

- Relicensed unreleased 1.1.0 code to `AGPL-3.0-or-later`; released 1.0.x tags remain MIT licensed.
- Preserved the 1.0.x facade while rebuilding request, response, resource, and service internals.
- Modernized Composer dependencies and development tooling.

### Deprecated

### Removed

- Tracked Composer dependencies under `vendor/` and reliance on `.env.test` for unit tests.

### Fixed

- Empty API keys are rejected.
- `setApiKey()` retains host, namespace, version, debug, and injected transport configuration.
- Tracking-status resources resolve with the correct spelling.
- Legacy resource lifecycle hooks execute in the documented order and retain caller options.
- Order services can access the transport, and QR/signature subject paths are generated correctly.
- HTTP status, malformed JSON, empty body, transport, and Fleetbase error responses are handled consistently without leaking credentials.

### Security


## [1.0.3] - 2022-05-25

### Added

- Additional order resource actions.

## [1.0.2] - 2022-01-03

### Changed

- Latest baseline whose public API is explicitly preserved by 1.1.0.


[Unreleased]: https://github.com/fleetbase/fleetbase-php/compare/1.1.0...HEAD
[1.1.0]: https://github.com/fleetbase/fleetbase-php/compare/1.0.3...1.1.0
[1.0.3]: https://github.com/fleetbase/fleetbase-php/compare/1.0.2...1.0.3
[1.0.2]: https://github.com/fleetbase/fleetbase-php/compare/1.0.1...1.0.2
