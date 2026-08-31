# 1.1.0 release checklist

The release workflow validates and packages the candidate automatically. Publication remains an explicit maintainer-approved operation.

## Maintainer decisions before publication

- Confirm Fleetbase has the rights required to publish the new release under `AGPL-3.0-or-later`.
- Approve a minimum mutation score and any narrowly justified exclusions. The first complete baseline measured 86.27% with 100% mutation-code coverage and no ignored source.
- Select protected `release` environment approvers and the signing/attestation identity.
- Verify Packagist ownership and the GitHub update hook.
- Approve the coordinated Fleetbase API-reference generator update.
- Approve the default-branch migration and the protected `master` transition period.

## Repository preparation

1. Merge the implementation pull request into `release/v1.1.0` after every pull-request check succeeds.
2. Review and merge the release integration into `main` after the default-branch runbook is approved.
3. Configure required checks and the protected `release` environment without granting workflow bypasses.
4. Add `POSTMAN_API_KEY`, run both disposable contract workflows, and retain their evidence.
5. Run the release workflow with version `1.1.0` and `publish=false`; review the archive, SBOM, checksums, coverage summary, API matrix, and workflow logs.

## Publication

1. Re-run the release workflow from the reviewed `main` commit with version `1.1.0` and `publish=true`.
2. Approve the protected `release` environment only after the validation job succeeds.
3. Confirm the immutable `1.1.0` tag and GitHub Release target the reviewed commit and contain the expected artifacts and provenance.
4. Verify GitHub and Packagist identify `AGPL-3.0-or-later` for 1.1.0 while older tags retain their original MIT terms.
5. Install the exact public package into clean plain PHP, Laravel, and Symfony fixtures:

```bash
composer require fleetbase/fleetbase-php:1.1.0
composer install --no-dev --optimize-autoloader
```

6. Confirm the public API reference renders the checked-in 1.1.0 PHP examples.

Never reuse, move, or rewrite a published tag. If validation fails after publication, publish a new patch release.
