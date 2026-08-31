# Repository and release-infrastructure audit

Snapshot date: 2026-08-31

This document records non-secret metadata used to prepare v1.1.0. Secret values were not requested or displayed.

## Published package baseline

- GitHub repository: `fleetbase/fleetbase-php`, public.
- GitHub default branch: `master`.
- Latest published tag discovered after refreshing official refs: `1.0.3`, dereferencing to commit `97dabc53fc1c9c96ce99fb2bd88ddbd04164b450`.
- Required compatibility baseline: `1.0.2`, commit `569fe88e0359d567f30588820243d1d69ab418ec`.
- The 1.0.3 commit and annotated tag are unsigned according to GitHub verification metadata.
- Packagist package: `fleetbase/fleetbase-php`; public metadata reported 33 total downloads, four favers, and zero dependents at audit time.
- Packagist exposes 1.0.3 and development branches. Packagist ownership, hook delivery state, and organization-level permissions are not publicly observable and require an owner to verify in Packagist.
- Existing published versions remain MIT-licensed. The requested AGPL change takes effect only in the new release after rights confirmation.

## Repository administration

- Administrators returned by the GitHub collaborators API: `roncodes`, `shivthakker`.
- `master` has no branch-protection rule.
- GitHub Actions environments: 0.
- GitHub Actions repository secrets: 0 names reported.
- GitHub Actions repository variables: 0 names reported.
- Active repository webhooks: one Packagist webhook. No credential-bearing URL was recorded.
- Secret scanning: enabled.
- Push protection: enabled.
- Non-provider pattern scanning and validity checks: enabled.
- Secret-scanning alerts: 0 at audit time.
- Dependabot security updates: disabled.
- GitHub's dependency alert reported on push: 21 known vulnerabilities on the default branch (8 high, 13 moderate).

## Tracked-content and credential assessment

- `vendor/` contains 4,394 tracked dependency files.
- `.phpunit.result.cache` and `.env.test` are tracked.
- The only `FLEETBASE_KEY` value in `.env.test` is an obvious placeholder by content and length; it is not being treated as a credential. The value is intentionally not reproduced here.
- No rotation or history rewrite is indicated by the available evidence. If a maintainer knows the placeholder was ever replaced with a real key in another revision, rotation and history remediation require a separately approved security operation.

## Human approvals still required

1. Confirm Fleetbase owns or has permission to relicense every contribution included in the new release as `AGPL-3.0-or-later`.
2. Verify Packagist owners and the GitHub update hook from the authenticated Packagist UI.
3. Select release-signing/attestation identity and protected-environment approvers.
4. Approve and apply repository rules, environments, required checks, and the final `master` to `main` default-branch change after workflows are ready.

## Planned remediations

The implementation will remove tracked dependencies and local state, regenerate dependencies from Composer, enable dependency update automation, add required CI/security/release workflows, and prepare repository-rule/default-branch instructions. Administrative changes and publication remain outside automation until explicitly approved.
