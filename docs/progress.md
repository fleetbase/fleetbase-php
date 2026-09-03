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
- Added configuration validation, declared legacy facade properties, injectable PSR-18 transport, response/error mapping, mutable resource state tracking, and repaired order-service access/path behavior.
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
- Fresh CI coverage advanced from 69.00% lines / 67.56% branches to complete ordinary executable coverage before the isolated terminating-helper probe closed the final legacy `Utils::dd()` gap. Exact uncovered line and branch diagnostics are emitted from Clover and serialized Xdebug evidence.
- The full CI dependency matrix remains green on PHP 7.4–8.5. Clean copied installs now pass locally for plain PHP, Laravel 12's container, and Symfony 7.4's dependency-injection and PSR-18 client, including optimized `--no-dev` autoload generation.
- Added machine-enforced AGPL metadata/source notices and distribution archive inspection. Security and release workflows are being completed with exact action commit pins, dependency review, Composer audit, secret scanning, CodeQL workflow analysis, SBOM generation, provenance, and a protected `release` environment.

At this checkpoint, remaining work included the final hard coverage gate, mutation testing, archive-based consumer installs, isolated Fleetbase/Postman contract CI, generated PHP examples, full documentation recipes, and release dry-run validation.

## 2026-08-31 — exact coverage and release evidence

- Executed the compatibility-preserved, process-terminating `Utils::dd()` helper in an isolated Xdebug subprocess and merged its real path data into PHPUnit's report; no source exclusion or ignored branch was added.
- Fresh CI run `33399571702` reports 100.00% classes (93/93), methods (450/450), branches (957/957), and lines (1,039/1,039). The hard 100.00% line/branch gate is now enabled for pull requests and releases.
- Built and inspected a source archive with 136 files and no forbidden development state. The 1.1.0 release identity check passes against latest tag 1.0.3.
- The first security run passed Composer audit/license verification and dependency review. It also exposed two actionable workflow findings: a test-only malformed URI triggered TruffleHog's unverified URI detector, and repository-level CodeQL default setup rejected a duplicate advanced SARIF upload. The fixture is now scanner-safe, while advanced Actions analysis remains locally enforced from SARIF without competing with default setup.
- Generated a first-class PHP example for each of the 220 locked requests. The exact fenced snippets execute through the public `Fleetbase` facade and a hermetic Guzzle transport in the unit suite (35 tests, 2,311 assertions), and generated-doc drift is part of `composer check`.
- Replaced the abbreviated README with configuration, self-hosting, PSR-18, services, errors, uploads/downloads, retries, diagnostics, Laravel, Symfony, compatibility, security, and license guidance backed by the implemented APIs.
- Changed consumer CI to download, inspect, extract, and install the built source archive before running the plain PHP, Laravel, and Symfony verifiers; path-based checkout installs no longer satisfy this gate.

Remaining automatable gates include mutation testing, isolated Fleetbase/Postman contract CI, and release dry-run validation. Human approval remains required for relicensing rights, the mutation threshold, protected release approvers/signing, Packagist verification, the API-reference repository change, and the final default-branch change.

## 2026-08-31 — disposable upstream contract gates

- Locked the full Fleetbase stack commit that contains the exact Core API and Fleet-Ops package revisions used by the 220-request SDK contract.
- Added a weekly/manual disposable-stack workflow that builds those locked sources, proves both package revisions, runs both official Postman collections, mints a fresh test credential, and exercises representative SDK success and failure paths against the same API instance.
- Added a separate weekly/manual drift workflow that compares the current official Postman collections with the SDK lock, uploads machine-readable evidence, and opens or updates a repository issue when requests are added, removed, or materially changed.
- The local drift comparison against the locked Postman revision passes across all 220 requests. Workflow syntax, PHP syntax/formatting, max-level PHPStan, generated-artifact drift, compatibility, and the complete hermetic test suite also pass locally.

The disposable native Postman run requires a repository or organization `POSTMAN_API_KEY`; its CI result is intentionally not claimed until the secret-backed workflow has executed. Release dry-run validation and mutation-survivor review remain automatable work. Human approval is still required for the policy and repository-administration items listed above.

## 2026-08-31 — mutation and secret-scan baseline review

- The first full-source Infection run generated 1,588 mutants: 1,370 killed, 214 escaped, 4 timed out, and none uncovered, errored, skipped, or ignored. Its measured mutation score indicator is 86.27% with 100% mutation-code coverage.
- CI now permits the four observed timeout-killed mutants with a narrow ceiling of five; additional timeout growth remains a failure. The permanent minimum mutation-score policy remains an explicit maintainer decision, while the complete report stays visible as a CI artifact.
- The secret workflow now separates verified-secret history scanning from verified-and-unknown scanning of the current source snapshot. This keeps full historical credential validation while preventing a repaired test-only URI false positive in an intermediate review commit from permanently blocking the branch without rewriting history.
- Pull-request CI now assembles the complete 1.1.0 release candidate only after quality, compatibility, exact coverage, mutation, archive, and all consumer gates pass. The standalone release workflow additionally audits dependencies and verifies that a published version becomes installable from Packagist.

## 2026-09-02 — pagination contract correction

- Maintainer review confirmed that Fleetbase API v1 does not expose an SDK pagination contract. Removed the unreleased speculative `Service::paginate()` method, its collection metadata abstraction, tests, README example, and release claims.
- `findAll()` and `query()` continue returning arrays for 1.0.x compatibility. They retain defensive hydration for bare arrays and recognized `data`, `results`, or `items` list envelopes, but do not page, slice, or automatically issue follow-up requests.

## 2026-09-03 — canonical disposable contract bootstrap

- Refactored the PHP SDK live contract to use the same published API image, clean database volume, pre-boot CI environment, non-interactive installer, health gate, and Fleetbase-owned seed-and-mint action used by the Core API and Fleet-Ops Postman workflows.
- Retained the SDK-specific bridge on the same runner so both official collections still execute through PHP SDK methods rather than bypassing the library; the evidence gate requires every one of the 220 locked requests.
- Added the canonical secret gate, optional Google Maps fixture configuration, test-only Stripe fixture inputs, API image digest reporting, and failure-safe stack diagnostics and teardown.

## 2026-09-03 — ergonomic generated endpoint signatures

- Classified all 220 locked requests by ordered URL identifiers and payload placement, then generated positional/direct calls for JSON, query, raw JSON, multipart, and empty-payload endpoints.
- Centralized overload normalization in the base service while preserving the full published 1.1.0 parameter-array envelope, including PHP 8 named `parameters:` and `options:` calls.
- Generated hermetic tests now invoke every endpoint in both forms and compare verb, encoded URL, query, JSON semantics, multipart content, and request-option forwarding. The focused edge-case suite rejects missing identifiers, invalid data/options, and ambiguous duplicate payloads.
- Added the authoritative 1.1.0 public API snapshot to the compatibility gate. Local PHPUnit evidence is 35 tests and 3,906 assertions; fresh Xdebug evidence remains exactly 100.00% lines and 100.00% branches for the 1.1.2 candidate.
- Switched the disposable bridge to positional/direct invocations so a successful 220-request run proves the documented SDK shape against Fleetbase rather than only exercising the legacy envelope.
- The fresh full-source mutation run generated 1,802 mutants: 1,583 killed, 215 escaped, four timed out, and none uncovered, errored, skipped, or ignored. MSI and covered-code MSI are both 87.85%, above the approved 85% floor, with 100% mutation-code coverage.
