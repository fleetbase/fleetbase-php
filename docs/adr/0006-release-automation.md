# ADR 0006: Review-gated release automation

- Status: accepted for implementation; signing authority pending maintainer approval
- Date: 2026-08-31

## Context

Existing tags are unsigned and releases have no automated validation, provenance, archive inspection, or post-publication installation check.

## Decision

Pull requests and release candidates will run the same authoritative quality gates. The release workflow will use a protected GitHub `release` environment, accept an explicit semantic version, require the commit on `main`, verify a clean tree and matching changelog, rebuild all evidence, then create an immutable tag and GitHub Release after maintainer approval.

Artifacts will include checksums, an SBOM, coverage and API-mapping summaries, and GitHub-supported provenance. Packagist will derive versions from VCS tags; the workflow will verify availability and a clean consumer install. A dry-run mode must execute every step except tag, release, and Packagist publication.

The signing mechanism and authorized release approvers must be selected by a maintainer before publication. Automation will never rewrite a released tag.

## Consequences

Release inputs and outputs become reproducible and reviewable. Publishing remains intentionally human-gated.
