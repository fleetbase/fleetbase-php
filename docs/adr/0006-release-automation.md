# ADR 0006: Review-gated release automation

- Status: accepted and implemented
- Date: 2026-08-31

## Context

Existing tags are unsigned and releases have no automated validation, provenance, archive inspection, or post-publication installation check.

## Decision

Pull requests and release candidates run the same authoritative quality gates. A push to `main` is eligible for release only when GitHub associates that exact commit with a merged pull request whose source branch starts with `release/`. The semantic version is derived from the remainder of the branch name. The workflow verifies a clean tree and matching changelog, reruns the disposable live SDK contract, rebuilds all release evidence, and then creates an immutable tag and GitHub Release through the protected `release` environment.

Artifacts include checksums, an SBOM, coverage and API-mapping summaries, and GitHub-supported provenance. Packagist derives versions from VCS tags; the workflow verifies availability and a clean consumer install. Pull-request CI assembles the release candidate without publishing, so no separate manual dry-run input is required.

Configured environment reviewers retain the final publication approval. Automation never rewrites a released tag.

## Consequences

Release inputs and outputs are reproducible and reviewable. Release initiation is automatic and branch-derived; publication remains subject to the configured environment policy.
