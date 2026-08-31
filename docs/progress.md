# v1.1.0 implementation progress

This log records completed checkpoints and evidence. A passing local check is not represented as CI success.

## 2026-08-31 — baseline refresh and contract freeze

- Refreshed official Git refs and found published 1.0.3, which was absent from the stale local `origin/master` reference.
- Merged current `origin/master` into `release/v1.1.0` at `e4a368d`; retained 1.0.2 as the required compatibility baseline.
- Locked Postman, Core API, Fleet-Ops, 1.0.2, and 1.0.3 commits in `contracts/contract-lock.json`.
- Generated public/protected API snapshots: 1.0.2 has 22 classes, 64 declared public/protected methods, and 13 facade runtime properties; 1.0.3 has 22 classes, 84 declared public/protected methods, and 13 facade runtime properties.
- Generated the Postman contract manifest: 220 requests across 36 groups (`84 GET`, `77 POST`, `25 PUT`, `6 PATCH`, `28 DELETE`). All begin as explicitly unmapped.
- Added six architecture decisions and the non-secret repository/release audit.
- Local generator syntax checks: pass on PHP 8.2.10.
- Deterministic manifest count check: pass (220 requests, 36 groups).
- Working tree passes the 1.0.3 public API check. The 1.0.2 check correctly detects the published `getDistanceAndTime()` parameter rename in 1.0.3; the compatibility implementation must restore the legacy named argument while accepting the 1.0.3 query form.

Remaining in Phase 0: characterize observable legacy behavior, add automated snapshot/manifest verification, establish backwards-compatibility tooling, and receive maintainer confirmation of relicensing rights. Phase 1 repository hygiene can proceed independently of the human licensing confirmation, but the new license cannot be published until confirmation.

## 2026-08-31 — hygiene and compatibility foundation in progress

- Removed 4,394 tracked `vendor/` files. The local `.env.test` and PHPUnit cache were confirmed ignored and untracked, rather than repository content at the current baseline.
- Widened runtime support to `^7.4 || ^8.0`; added PSR HTTP contracts and maintained development tools. Composer strict validation passes and a fresh advisory audit reports zero known vulnerabilities in the new lock.
- Replaced live, credential-dependent tests with a hermetic Guzzle mock suite: pass on PHP 8.2 / PHPUnit 11.5 with 5 tests, 45 assertions, no deprecations.
- Added canonical AGPL v3 text, `AGPL-3.0-or-later` Composer/source notices, migration warning, README/badges, community health files, Dependabot configuration, and a pinned-action baseline CI matrix.
- Added configuration validation, declared legacy facade properties, injectable PSR-18 transport, response/error mapping, mutable resource state tracking, pagination, and repaired order-service access/path behavior.
- Public API compatibility checks pass against both 1.0.2 and 1.0.3. The contract manifest structure passes with all 220 requests still explicitly unmapped.
- PHPStan 2.2 at max initially reported 307 errors across source/tests after excluding standalone build tools. The foundation refactor resolved all 307 without a baseline or ignore entry; max-level analysis now passes.

The aggregate `composer check` gate now passes locally: syntax, formatting, max-level analysis, hermetic tests, contract structure, and compatibility against 1.0.2/1.0.3. Remaining work includes exhaustive transport/resource tests, fresh 100% line/branch coverage, the full endpoint service mapping, framework fixtures, and expanded security/release workflows.

## 2026-08-31 — official endpoint surface mapped

- Mapped all 220 locked Postman requests to 35 explicit service classes and checked-in endpoint methods; the 36 source groups collapse to 35 services because both collections contain the Organizations group.
- Added deterministic placeholder handling for both `{{name}}` and Postman `:name` path forms, including URL encoding and required-parameter failures.
- Added a hermetic transport contract that executes every manifest row and asserts the HTTP verb and resolved path. The manifest validator now proves each implementation and test reference exists.
- Added dedicated facade service instances and accessors for every in-scope group while preserving every 1.0.2 and 1.0.3 entry point.
- Added concrete resource hydration classes for every service group and opt-in retry behavior limited to safe/idempotent requests, including `Retry-After` handling.
- Current local evidence: PHPStan max passes, PHPUnit 11.5 passes with 13 tests and 1,489 assertions, all 220 mappings are complete, and API compatibility passes against 1.0.2 and 1.0.3.

Fresh Xdebug line/branch coverage is being added as a visible CI diagnostic before the 100.00% hard threshold is enabled. Remaining work includes exhaustive lifecycle/utility branches, request and response fixtures from official examples, consumer/framework fixtures, mutation testing, security/release workflows, API-reference coordination, and human-gated repository/release administration.

## 2026-08-31 — request fixtures, coverage, and consumer integration

- Enriched the locked manifest with official descriptions, authentication modes, path/query/body fixtures, and 127 response-example references. Generated a 220-row API coverage matrix, and made generated service/tests/docs drift a CI failure.
- Deterministic contract tests now validate official JSON, query, and multipart request fixtures in addition to every method and path. PHPUnit 11.5 currently passes with 34 tests and 1,869 assertions.
- Fresh CI coverage advanced from 69.00% lines / 67.56% branches to 100% of ordinary executable lines and 99%+ branches. Exact uncovered line and branch diagnostics are emitted from Clover and serialized Xdebug evidence. The only remaining uncovered lines are the process-terminating legacy `Utils::dd()` helper; its treatment is part of the documented compatibility-shim approval decision.
- The full CI dependency matrix remains green on PHP 7.4–8.5. Clean copied installs now pass locally for plain PHP, Laravel 12's container, and Symfony 7.4's dependency-injection and PSR-18 client, including optimized `--no-dev` autoload generation.
- Added machine-enforced AGPL metadata/source notices and distribution archive inspection. Security and release workflows are being completed with exact action commit pins, dependency review, Composer audit, secret scanning, CodeQL workflow analysis, SBOM generation, provenance, and a protected `release` environment.

Remaining automatable gates include exact 100% branch enforcement, the approved treatment or isolated coverage merge for `Utils::dd()`, mutation testing, archive-based consumer installs, isolated Fleetbase/Postman contract CI, generated PHP examples, full documentation recipes, and release dry-run validation. Human approval remains required for relicensing rights, mutation/exclusion policy, protected release approvers/signing, Packagist verification, and the final default-branch change.
