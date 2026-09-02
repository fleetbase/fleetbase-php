# Fleetbase PHP SDK v1.1.0 Modernization Plan

Status: proposed for maintainer review

Prepared: 2026-08-31

Target: a backwards-compatible `1.1.0` release, followed by an optional `2.0.0` cleanup only after a documented deprecation window

## Executive summary

The current SDK is a 2022 prototype rather than a production-ready client library. It has no repository CI or release automation, vendors 4,394 dependency files, supports PHP 7.4 but excludes every PHP 8 release through its Composer constraint, performs destructive integration tests against a live API, and exposes only a fraction of the current Fleetbase API. The modernization will also move the project from MIT to `AGPL-3.0-or-later`, matching current Fleetbase Core API and Fleet-Ops licensing.

The official merged Postman collections currently define 220 requests across 36 collection groups:

- Fleetbase API: 196 requests (`75 GET`, `70 POST`, `22 PUT`, `6 PATCH`, `23 DELETE`) across 32 groups.
- Fleetbase Core API: 24 requests (`9 GET`, `7 POST`, `3 PUT`, `5 DELETE`) across 4 groups.

By comparison, the SDK exposes 12 top-level stores and 14 resource classes. Its sole specialized service, `OrderService`, cannot execute because it accesses private members of its parent class. The modernization must therefore rebuild the internals, preserve the existing public entry points, add the complete documented contract, and establish repeatable quality and release gates.

This plan treats the Postman Native Git collections as the public contract inventory and uses Core API and Fleet-Ops routes, controllers, request validation, and resources to verify behavior. No SDK method should be invented from a route name alone.

## Evidence reviewed

### SDK baseline

- Repository: `fleetbase/fleetbase-php`
- Default branch: `master`
- Required compatibility baseline: `569fe88` / `1.0.2`
- Latest published tag after refreshing official refs: `97dabc5` / `1.0.3`
- Baseline commit date: 2022-01-03
- The 1.0.3 delta adds order actions that are also part of the compatibility surface; it changes the second `OrderService::getDistanceAndTime()` argument and contains malformed QR/signature path expressions, so callability and corrected behavior both require regression tests.
- Production source and tests: 1,307 lines across 25 PHP files
- Tracked dependencies: 4,394 files under `vendor/`
- CI/release configuration: none
- Local checks on PHP 8.2.10:
  - syntax lint: pass
  - Composer validation: pass
  - PSR-12: fail with 23 errors and 1 warning
  - PHPStan 0.11.20: fail with 28 errors and PHP 8.2 deprecations
  - PHPUnit: intentionally not run because the suite reads a local API key and creates, updates, and deletes live resources
  - Composer audit: not completed because the sandbox could not reach Packagist; this remains a required first implementation check
  - GitHub dependency baseline reported during the branch push: 21 known vulnerabilities on the default branch (`8 high`, `13 moderate`)

### Contract sources

The inventory was taken from the merged/default branches at these exact local references:

- `fleetbase/postman` `origin/main` at `43253dbf87e5030d95d12be019dc26fcb7151ed6`
- `fleetbase/core-api` `origin/main` at `b7691c06ffdfe8f8874352e746aa8e523d5e3531` (`v1.6.60`)
- `fleetbase/fleetops` `origin/main` at `a9131daeb1a23ed4b0046dd2d7b632fb100bfba0` (`v0.6.61`)

Public sources:

- [Fleetbase Postman collections](https://github.com/fleetbase/postman)
- [Fleetbase Core API](https://github.com/fleetbase/core-api)
- [Fleet-Ops](https://github.com/fleetbase/fleetops)
- [Fleetbase API reference](https://fleetbase.io/docs/api)
- [Composer library guidance](https://getcomposer.org/doc/02-libraries.md)
- [Packagist publishing and update hooks](https://packagist.org/about)
- [PHPUnit coverage metrics](https://docs.phpunit.de/en/13.4/code-coverage.html)

The API reference generator already maps PHP SDK examples for only 13 Fleetbase stores and falls back to raw HTTP elsewhere. Core API has no PHP SDK mapping. Completion of this plan must also update that generator so published examples represent real SDK methods.

## Findings and feedback

### 1. Public API and runtime defects

The current public shape must be captured before refactoring, but known defects must not be enshrined as correct behavior.

- `composer.json` requires `php: ^7.4`, which means `>=7.4 <8.0`; the README claim of “PHP 7.4 and later” is incorrect.
- `Fleetbase` assigns undeclared properties such as `$places` and `$orders`. These are dynamic properties and are deprecated on PHP 8.2.
- API-key validation can never reject an empty key because the constructor type already guarantees a string and the condition uses `&&` with `!is_string()`.
- `setApiKey()` creates a new client but silently loses the existing host, namespace, version, and debug configuration.
- `trackingStatuses` is configured with `TrackingStatues`, causing resource resolution to target a class that does not exist.
- Every concrete resource declares `__constructor()` rather than `__construct()`. These methods never run.
- `OrderService` calls the private `Service::uri()` method and reads the private `$client` property.
- `Order` reads the private `Resource::$service` property.
- `Resource::create()` builds request hooks and then discards them by passing `[]` to the service.
- `Resource::save()` discards its `$options` argument.
- Resource dirty state, changes, lifecycle flags, reload behavior, and flag setter exist only as unused fields or empty methods.
- `HttpClient` invokes `onBefore` after the network request.
- HTTP failures are detected only when a decoded object has an `error` property. Status codes, validation envelopes, malformed JSON, empty bodies, transport failures, and response metadata are not modeled safely.
- `getLastResponse()` is typed as Guzzle's concrete response while Guzzle returns the PSR response interface.
- List/query handling supports only a bare JSON array and does not defensively recognize common response envelopes. Fleetbase API v1 does not define an SDK pagination contract.
- File upload/download, multipart bodies, streams, custom headers, idempotency keys, retries, timeouts, and cancellation are unsupported.
- URL and namespace configuration is insufficient for self-hosted installations with path prefixes.

### 2. Contract completeness gap

The SDK exposes these stores today:

`orders`, `entities`, `places`, `drivers`, `vehicles`, `vendors`, `contacts`, `serviceAreas`, `zones`, `trackingStatuses`, `serviceRates`, and `serviceQuotes`.

The Postman Fleetbase collection also covers customers, devices, equipment, fleets, fuel reports, fuel transactions, geofences, issues, labels, manifests, onboarding, orchestrator operations, order configs, organizations, parts, payloads, purchase rates, sensors, tracking numbers, work orders, and numerous resource-specific actions. The Core collection adds chat channels, comments, files, and current-organization operations.

Even existing stores are incomplete. Orders alone have 30 documented requests, including dispatch, start, cancel, activity, destination, signature, QR, route, proof, schedule, and related-resource operations. A generic CRUD service cannot represent these actions clearly or safely.

### 3. Test and quality risks

- Only two test classes exist.
- Tests instantiate the real client using `.env.test` and mutate API state.
- There are no mock transport tests, exception-path tests, serialization tests, list-response tests, compatibility tests, or contract tests.
- The suite does not produce trustworthy coverage evidence for the current source.
- PHPUnit 8 and PHPStan 0.11 are obsolete.
- The PHPUnit configuration uses the removed `filter/whitelist` schema.
- No test verifies installation from a clean Composer consumer project.
- No test protects the existing public class names, constructor signatures, dynamic store access, resource methods, or return shapes.

### 4. Package and repository hygiene

- `vendor/`, `.env.test`, and `.phpunit.result.cache` are committed.
- The published archive therefore carries development dependencies and local test configuration.
- README links point to missing or stale files and services, including Travis CI and `master`-based badges.
- README badges do not represent current CI, coverage, PHP support, branch naming, or license status.
- There is no `SECURITY.md`, `CONTRIBUTING.md`, code of conduct, support policy, issue template, PR template, funding/config metadata, or Dependabot/Renovate setup at the repository level.
- `CHANGELOG.md` does not provide a reliable release history or migration guidance.
- There are no `.gitattributes` export rules, so package archives are not intentionally minimized.
- The repository and Composer metadata currently declare MIT; the requested move to AGPL v3 must be applied consistently and must not misrepresent the terms of already-published releases.

### 5. Release and supply-chain gaps

- Releases are hand-tagged with no automated validation.
- There is no proof that the tag matches a reviewed commit.
- There is no automated GitHub Release, changelog generation, SBOM, provenance/attestation, or Packagist sync verification.
- There is no dependency vulnerability scan, dependency review, CodeQL, secret scan, or pinned-action policy.
- There are no branch protections or required checks represented in repository files; repository settings must be audited separately by an administrator.

## Compatibility policy

### v1.1.0 promise

`1.1.0` will preserve these existing consumer entry points:

- namespace `Fleetbase\Sdk`
- `new Fleetbase(string $publicKey, array $config = [], bool $debug = false)`
- existing top-level store property names
- `Fleetbase::newInstance()`, `setApiKey()`, `getVersion()`, and `getOptions()`
- `HttpClient` verb methods and host/namespace accessors
- generic `Service` CRUD/query method names
- `Resource` lifecycle and attribute method names
- all existing resource class names

The implementation may correct behavior that is demonstrably unusable, such as inaccessible private members and misspelled constructors. Each correction must have a regression test and a changelog entry. New return or parameter types must not make subclassing more restrictive in the `1.x` line.

### Compatibility verification

Before changing production code:

1. Generate a reflection-based API snapshot of every public/protected class, method, property, signature, and default value at `1.0.2`.
2. Add characterization tests for observable `1.0.2` behavior that consumers could reasonably depend on.
3. Add a backwards-compatibility checker against the latest stable tag.
4. Run real fixture applications using the old README examples and representative subclass/injected-client cases.
5. Classify every compatibility difference as additive, bug fix, deprecation, or breaking change.

Any unavoidable breaking change moves to `2.0.0`; it must not be hidden in `1.1.0`.

### PHP support

The preferred bridge constraint is `^7.4 || ^8.0`, verified on PHP 7.4, 8.0, 8.1, 8.2, 8.3, 8.4, and 8.5 with both lowest and highest dependency sets. This widens actual compatibility without immediately abandoning installed 1.0 consumers.

PHP 7.4 and 8.0 are end-of-life. They should receive compatibility testing only, with an explicit support notice that production users must run a security-supported PHP version. A later `2.0.0` may set the runtime floor to the oldest security-supported PHP version after usage and framework compatibility are reviewed.

## Target architecture

### Stable facade

Keep `Fleetbase` as the backwards-compatible facade. Declare all existing stores explicitly and add the missing stores with documented types. A registry/lazy resolver may construct services internally, but property names must remain stable.

Add explicit accessors alongside property access so static analysis and dependency injection work without magic:

```php
$fleetbase->places;
$fleetbase->places();
```

The first form preserves compatibility; the second is the preferred modern API.

### Transport boundary

Split request construction, transport, response decoding, and error mapping.

- Depend on PSR-18 (`psr/http-client`) and PSR-7 request/stream factories at the boundary.
- Keep Guzzle as the zero-configuration default and preserve `HttpClient` as the compatibility adapter.
- Allow injected PSR-18 clients so Symfony HttpClient, Laravel-managed Guzzle clients, HTTPlug-compatible transports, and test doubles can be used.
- Introduce immutable client configuration for base URI, namespace/version, API key, user agent, timeouts, retry policy, headers, debug behavior, and idempotency keys.
- Preserve self-hosted path prefixes and normalize slashes without altering URLs.
- Provide JSON, query, form, multipart, stream, and download request modes.
- Expose response status, headers, request ID, and raw PSR response when needed.

### Error model

Introduce a `FleetbaseException` hierarchy while keeping the existing base exception catchable:

- authentication/authorization
- validation, including field errors
- not found/conflict/rate limit
- server response
- transport/timeout
- decoding/unexpected response

Exceptions should retain status, Fleetbase error code/message, validation details, request ID, method, sanitized URL, and the previous exception. They must never include API keys or sensitive payload values in messages.

### Services and resources

Use a common typed service for standard CRUD/query behavior and dedicated services for every custom endpoint. Keep resources lightweight and predictable:

- immutable response snapshot plus controlled local changes
- nested object/array hydration without lossy casts
- `ArrayAccess`, `JsonSerializable`, and explicit `toArray()` only if added without changing legacy attribute access
- correct dirty/change tracking
- save, reload, and destroy state transitions
- stable access to the owning service
- defensive list-response hydration that preserves the legacy array return without inventing SDK-side pagination

Avoid generating opaque magic methods from endpoint names. A checked-in contract manifest may be generated from Postman, but human-reviewed service methods should define the public SDK experience.

### Endpoint coverage model

Create `docs/api-coverage.md` as a machine-checkable matrix with one row for every Postman request:

| Field | Purpose |
|---|---|
| Collection/ref | exact source commit |
| Group and request | stable contract identity |
| Method and path | transport contract |
| Authentication | API key, driver/customer token, or special flow |
| SDK service/action | public PHP entry point |
| Request fixture | validated happy-path input |
| Response fixture | success hydration shape |
| Error fixtures | 401/403/404/422/429/5xx as applicable |
| Unit test | deterministic transport/serialization test |
| Contract test | live implementation verification |
| Status | missing, partial, complete, or intentionally raw-only |

Completion means every one of the 220 requests is mapped to a first-class method or has an explicitly approved raw-transport rationale. “The generic client can technically call it” is not sufficient for a documented resource action.

## Implementation workstreams

### Phase 0 — freeze and baseline

Deliverables:

- Record the exact Postman, Core API, and Fleet-Ops refs in a contract lock file.
- Capture the `1.0.2` public API snapshot and behavioral characterization suite.
- Add Architecture Decision Records for PHP support, PSR transport, list responses/resources, endpoint generation, exceptions, and release automation.
- Audit Packagist ownership, auto-update status, download metadata, and the unpublished/visible contents of `1.0.2`.
- Audit repository administrators, branch protection, environments, secrets, webhooks, and signing policy without exposing secret values.
- Confirm Fleetbase has the rights required to relicense all existing SDK code and record the effective release; previously published MIT tags remain under their original license.
- Decide whether the existing `.env.test` value was ever real; if so, rotate it outside the repository work and purge it from history in a separately approved security operation.

Exit gate: the public surface and release infrastructure are documented, secrets risk is resolved, and the contract refs are reproducible.

### Phase 1 — repository and toolchain hygiene

Deliverables:

- Remove tracked `vendor/`, `.env.test`, and PHPUnit cache files from the next commit and cover them in `.gitignore`/`.gitattributes`.
- Retain `composer.lock` for reproducible contributor tooling, but exclude it from distribution archives if the maintainer adopts that library policy.
- Refresh Composer metadata: precise description, minimum-stability policy, `prefer-stable`, funding/support links, autoload exclusions, and a platform/config policy.
- Replace the MIT license with the canonical GNU Affero General Public License v3 text, set Composer's SPDX identifier to `AGPL-3.0-or-later`, update source headers/notices and documentation, and verify GitHub and Packagist detect the new license on the release branch/tag.
- Upgrade PHPUnit, PHPStan, coding standard tooling, Mockery if retained, and coverage dependencies using PHP-version-specific dev resolution where necessary.
- Add PHP-CS-Fixer or a maintained PSR-12 rule set, EditorConfig, static-analysis config, and Composer scripts with consistent names.
- Add `SECURITY.md`, `CONTRIBUTING.md`, `CODE_OF_CONDUCT.md`, support policy, templates, and a current README. Replace stale Travis/`master` badges with verified badges for current CI, coverage, PHP support, Packagist release/downloads, and `AGPL-3.0-or-later`; every badge must link to its authoritative report or package page.
- Add Dependabot or Renovate for Composer and GitHub Actions.

Exit gate: a fresh clone installs with Composer, contains no vendored dependencies or secrets, and all local non-network checks are green.

### Phase 2 — transport and compatibility foundation

Deliverables:

- Introduce configuration, request builder, PSR transport adapter, response decoder, error mapper, and sanitized diagnostics.
- Reimplement the existing `HttpClient` API on the new internals.
- Declare existing facade properties and correct resource construction without changing consumer names.
- Add raw `request()` escape hatch for forward compatibility.
- Support injected PSR-18 clients plus default Guzzle.
- Correct hooks with a documented order and preserve legacy hook names.
- Add retries only for safe/idempotent requests or requests carrying an idempotency key; honor `Retry-After` and make retry policy opt-in/configurable.

Exit gate: old examples and characterization fixtures pass on all supported PHP versions, and transport/error tests cover every branch.

### Phase 3 — standard resource completion

Implement and test standard CRUD/query services for all public collection groups, including the currently missing groups. Add filtering, sorting, includes, sparse fields where supported, and resource hydration based on actual response resources. Do not add an SDK pagination abstraction until the API exposes a verified pagination contract.

Suggested batches:

1. Existing surface: places, service areas, zones, contacts, vendors, vehicles, drivers, orders, entities, service rates, service quotes, tracking statuses.
2. Existing models not exposed: payloads and waypoints.
3. Fleet operations: fleets, equipment, fuel reports, fuel transactions, issues, manifests, parts, purchase rates, sensors, tracking numbers, work orders.
4. Identity/organization: customers, devices, organizations, onboarding.
5. Core collaboration: comments, files, and chat channels.

Exit gate: every standard request is present in the coverage matrix and has deterministic success/error tests.

### Phase 4 — custom action completion

Implement specialized methods for non-CRUD operations, using Postman names and server behavior as the traceability keys. This includes, but is not limited to:

- Orders: dispatch, start, cancel, activity transitions, next activity, destination, route/distance, schedule, proofs, QR/signature capture, and related resources.
- Drivers/customers: authentication flows, verification, password reset/change, device registration, organization switching, online/track actions, customer orders and places.
- Devices/vehicles/vendors: attach/detach, assignments, and status/action endpoints.
- Fuel transactions/reports, geofence reporting, manifests, labels, tracking numbers, work orders, service quotes, orchestrator, and onboarding actions.
- Core files: multipart upload, base64 upload, metadata update, download/stream, and delete.
- Core chat/comments: participants, messages, read/delete actions, subject queries, and lifecycle operations.

Use explicit request DTOs only where they improve validation and discovery without preventing array-based legacy calls. Continue accepting arrays in the `1.x` line.

Exit gate: all 220 Postman requests have an approved mapping and the public docs generator emits valid PHP samples for each supported method.

### Phase 5 — documentation and framework integration

Deliverables:

- Rewrite README quick start, configuration, self-hosted base URL, error handling, list/query behavior, uploads/downloads, retries, debugging, and migration sections.
- Document the license change prominently in the README, changelog, migration guide, release notes, Composer metadata, and GitHub Release so downstream users can assess AGPL obligations before upgrading.
- Add framework recipes for plain PHP, Laravel container/service provider configuration, Symfony service configuration, and generic PSR-11/PSR-18 applications without forcing framework dependencies.
- Verify Composer 2.2 LTS/current installation, prefer-lowest/current dependency resolution, Packagist dist installation, Git VCS installation, and optimized/no-dev autoloading.
- Add PHP SDK mappings to the Fleetbase API reference generator for Core and every newly supported store/action in a coordinated `fleetbase/fleetbase.io` PR.
- Correct public claims that the SDK already has typed access to all core resources until the matrix is complete.

Exit gate: every documented snippet is executed in CI and the generated Fleetbase API reference contains no stale or fictional PHP call.

### Phase 6 — CI, security, and release automation

Required workflows, with third-party actions pinned to reviewed commit SHAs:

#### Pull request CI

- Composer strict validation and lock consistency.
- PHP syntax and coding-standard checks.
- PHPStan at max level with no ignored baseline growth.
- Unit/fixture tests on PHP 7.4–8.5.
- lowest-supported and latest-compatible dependency jobs.
- 100% line coverage and 100% branch coverage using Xdebug on the designated coverage runtime.
- mutation testing threshold for behavior quality; surviving mutations require review even when line coverage is 100%.
- backwards-compatibility comparison against the latest stable tag.
- clean consumer installs for plain PHP, Laravel, and Symfony fixture applications.
- dependency review, Composer audit, CodeQL, secret scanning, and license checks.
- built archive inspection proving excluded files are absent.

#### Contract CI

- Opt-in/integration workflow against an ephemeral Fleetbase stack pinned to the contract refs.
- Run the official Fleetbase and Core Postman collections first to prove the server fixture.
- Run SDK contract cases against the same stack.
- Never use production credentials or mutate a shared environment.
- Scheduled contract-drift job compares the locked Postman manifest to current `main` and opens an issue or PR when requests change.

#### Release workflow

- Use a protected GitHub `release` environment with maintainer approval.
- Accept an explicit semantic version and verify it is greater than the latest tag.
- Require the release commit to be on `main`, the worktree to be clean, all required checks to be successful, and the changelog/version notes to match.
- Re-run the authoritative test, coverage, compatibility, archive, and security gates.
- Create the tag and GitHub Release from the reviewed commit; do not publish from an arbitrary branch.
- Attach checksums, SBOM, coverage summary, API coverage matrix, and provenance/attestation where GitHub supports the artifact type.
- Let Packagist derive the version from the VCS tag; verify the Packagist GitHub hook updates the package and that a clean consumer can install the exact version.
- If post-publication verification fails, mark the release and publish a corrective version; never move or overwrite a published tag.

Exit gate: a dry-run release candidate completes without secrets in logs, and a maintainer-approved `1.1.0` release installs from Packagist into clean fixture applications.

### Phase 7 — default branch migration

The branch rename is a repository administration operation and should occur only after workflows and documentation are prepared.

1. Create `main` at the reviewed `master` head.
2. Update workflow triggers, branch protections, required checks, release rules, CodeQL/default setup, Dependabot, badges, links, templates, API docs, Packagist metadata, and external integrations to `main`.
3. Change the GitHub default branch to `main`.
4. Verify new clones, PR targets, GitHub Pages/docs, Packagist, and release automation.
5. Publish local-clone migration commands in the release notes.
6. Keep `master` temporarily protected and non-writable if external consumers need a transition period, then remove it only after confirming no required integration still targets it.

Exit gate: GitHub reports `main` as default, all required checks run on it, Packagist resolves `dev-main`, and no maintained link or workflow targets `master`.

## Test strategy and definition of 100%

“100% test coverage” means all of the following, not just a badge:

- 100% executable line coverage over `src/`.
- 100% branch coverage over `src/` using Xdebug.
- Every public method has success, validation/error, and boundary tests appropriate to its behavior.
- Every transport verb/body mode is tested for request method, URL, headers, sanitized authentication, query/body encoding, and response decoding.
- Every exception type is tested with representative server and transport failures.
- Every resource lifecycle transition is tested.
- Every Postman request has a coverage-matrix entry and a deterministic fixture test.
- Contract tests exercise representative happy and failure paths against an isolated real API; destructive workflows clean up their own fixtures.
- Mutation testing prevents assertion-free execution from satisfying the numeric target.
- Coverage is generated fresh in CI, uploaded as Clover/XML plus human-readable summary, and enforced with a hard failure below 100.00%.

Recommended layers:

1. Unit tests with PSR/Guzzle mock transports: fast, hermetic, exhaustive.
2. Serialization/hydration golden fixtures derived from sanitized official examples.
3. Consumer compatibility fixtures for old usage and framework integration.
4. Isolated integration tests against a disposable Fleetbase stack.
5. Official Postman contract run as the server-side prerequisite and drift oracle.

## Pull request and delivery structure

Do not implement this as one unreviewable rewrite. Use the release branch as the integration target and land bounded PRs in this order:

1. Baseline, compatibility snapshot, and repository hygiene.
2. CI/toolchain with the old implementation still characterized.
3. Transport/configuration/error internals behind the legacy facade.
4. Existing service/resource parity.
5. Missing standard resources in small domain batches.
6. Custom actions in small domain batches.
7. Core collaboration/files and multipart/streaming support.
8. Docs, examples, framework fixtures, and API-reference coordination.
9. Release automation, release candidate, and Packagist verification.
10. Default-branch migration.

Every implementation PR must include:

- contract source/ref and endpoints affected
- compatibility classification
- tests and fresh coverage evidence
- static analysis/coding-standard result
- API coverage matrix delta
- documentation impact
- security/credential impact
- exact validation commands and known limits

## Release acceptance checklist

`1.1.0` is ready only when:

- [ ] All 220 official Postman requests are mapped or explicitly approved as raw-only.
- [ ] All existing public entry points pass the compatibility suite.
- [ ] PHP 7.4–8.5 and lowest/latest dependency jobs pass.
- [ ] Line and branch coverage are both 100% with fresh CI artifacts.
- [ ] Mutation results meet the approved threshold with no unexplained survivors in critical transport/auth/error code.
- [ ] PHPStan max and coding standards pass without a growing baseline.
- [ ] Official Postman and SDK contract suites pass against the same disposable stack.
- [ ] Plain PHP, Laravel, and Symfony fixture installs pass from the built archive.
- [ ] Composer audit, dependency review, CodeQL, secret scan, and license checks pass.
- [ ] The archive contains no `vendor/`, credentials, caches, tests fixtures with secrets, or local paths.
- [ ] README, authoritative badges, changelog, migration guide, API coverage matrix, and generated API examples are current.
- [ ] `LICENSE`, Composer metadata, source notices, GitHub detection, Packagist metadata, and release notes consistently report `AGPL-3.0-or-later`, while old tags retain their original MIT terms.
- [ ] Release automation completes a no-publish dry run.
- [ ] A maintainer approves the protected release environment and the GitHub/Packagist release is verified after publication.
- [ ] `main` is the default branch and all integrations have been verified against it.

## Decisions requiring maintainer approval

These choices should be confirmed during Phase 0 before implementation diverges:

1. Whether `1.1.0` must retain runtime compatibility with end-of-life PHP 7.4/8.0 or whether modernization should be a `2.0.0` release with a supported-PHP floor.
2. Whether the SDK scope is strictly the 220 Fleetbase/Core requests or should also add separate Storefront and Ledger clients. This plan excludes Storefront and Ledger because their collections use distinct prefixes and currently have no PHP SDK mapping.
3. Whether `composer.lock` remains committed for contributor reproducibility; it must not dictate dependency versions for consumers.
4. Whether resource objects remain mutable in `1.x`; this plan preserves mutation and defers any immutable-only API to `2.x`.
5. Required mutation score and whether non-critical compatibility shims may be excluded with written justification.
6. Release signing/attestation policy and who approves the protected release environment.
7. Length of the protected `master` transition period after `main` becomes default.

## Out of scope for this planning pull request

This document does not modify production SDK code, dependencies, Git history, repository settings, Packagist settings, credentials, tags, releases, or the default branch. Those changes require the gated implementation work above. In particular, removing a possibly real key from Git history or rotating it must be handled as a separately approved security operation.
