# 1.3.0 release checklist

The release workflow starts automatically when a semantic `release/` branch is merged into `main`. Publication remains subject to the protected `release` environment policy.

## Maintainer decisions before publication

- Fleetbase ownership and authorization to publish the new release under `AGPL-3.0-or-later` was confirmed by the maintainer.
- An 85% minimum mutation score was approved. Measure the 1.3.0 candidate and record the figure here before publication; the 1.1.2 candidate measured 87.85% with 100% mutation-code coverage, four timeouts, and no ignored source.
- Select protected `release` environment approvers and the signing/attestation identity.
- Verify Packagist ownership and the GitHub update hook.
- Review the coordinated Fleetbase API-reference generator update.

## Repository preparation

1. Create `release/1.3.0` from `main` after the workflow repairs, update its dated changelog section and `docs/releases/1.3.0.md`, and open a recovery pull request to `main`. The original `release/v1.3.0` attempt did not publish a release.
2. Require every pull-request check, including the disposable 264-request SDK contract, before merge.
3. Configure required checks and the protected `release` environment without granting workflow bypasses.
4. Add `POSTMAN_API_KEY` at repository or organization scope and retain the live-contract artifacts.
5. Review the pull-request release-candidate archive, SBOM, checksums, coverage summary, API matrix, and workflow logs.

## Publication

1. Merge the reviewed `release/1.3.0` recovery pull request into `main`; this automatically starts the repaired release workflow and derives version `1.3.0`.
2. Confirm the live SDK contract and validation jobs pass, then approve the protected `release` environment if an approval rule is configured.
3. Confirm the immutable `1.3.0` tag and GitHub Release target the reviewed commit and contain the expected artifacts and provenance.
4. Verify GitHub and Packagist identify `AGPL-3.0-or-later` for 1.3.0 while 1.0.x tags retain their original MIT terms.
5. Install the exact public package into clean plain PHP, Laravel, and Symfony fixtures:

```bash
composer require fleetbase/fleetbase-php:1.3.0
composer install --no-dev --optimize-autoloader
```

6. Confirm the public API reference renders the checked-in 1.3.0 PHP examples.

Never reuse, move, or rewrite a published tag. If validation fails after publication, publish a new patch release.

## Release workflow troubleshooting

- Coverage and mutation jobs, including release validation, pin Xdebug 3.5.3 and verify the loaded version. Update this pin deliberately in all three jobs with fresh coverage and mutation evidence; do not lower the 100% line/branch or 85% mutation gates to accommodate tooling drift. Coverage artifacts are uploaded even when the coverage gate fails.
- Release detection retries GitHub's commit-to-PR lookup six times, ten seconds apart, to allow merge metadata to become visible. Only a merged `release/` PR targeting `main` whose merge SHA exactly matches the triggering commit is eligible. API errors fail the job; exhausted successful lookups with no matching PR produce an explicit warning and skip publication.
- An ordinary fix PR does not trigger a release. After merging workflow repairs, use a fresh reviewed release PR for the still-unpublished version. Rerunning an older workflow run uses that run's original workflow and source, not workflow repairs merged afterward. No tag should be created manually to bypass validation or the protected release environment.

Run the detector's offline regression suite with `node --test tools/tests/release-detection.test.mjs`. It uses simulated GitHub responses and does not create releases or require credentials.
