# 1.1.1 release checklist

The release workflow starts automatically when a semantic `release/` branch is merged into `main`. Publication remains subject to the protected `release` environment policy.

## Maintainer decisions before publication

- Fleetbase ownership and authorization to publish the new release under `AGPL-3.0-or-later` was confirmed by the maintainer.
- An 85% minimum mutation score was approved. The current 1.1.1 candidate measures 87.85% with 100% mutation-code coverage, four timeouts, and no ignored source.
- Select protected `release` environment approvers and the signing/attestation identity.
- Verify Packagist ownership and the GitHub update hook.
- Review the coordinated Fleetbase API-reference generator update.

## Repository preparation

1. Create `release/v1.1.1`, update its dated changelog section and `docs/releases/1.1.1.md`, and open a pull request to `main`.
2. Require every pull-request check, including the disposable 220-request SDK contract, before merge.
3. Configure required checks and the protected `release` environment without granting workflow bypasses.
4. Add `POSTMAN_API_KEY` at repository or organization scope and retain the live-contract artifacts.
5. Review the pull-request release-candidate archive, SBOM, checksums, coverage summary, API matrix, and workflow logs.

## Publication

1. Merge the reviewed `release/v1.1.1` pull request into `main`; this automatically starts the release workflow and derives version `1.1.1`.
2. Confirm the live SDK contract and validation jobs pass, then approve the protected `release` environment if an approval rule is configured.
3. Confirm the immutable `1.1.1` tag and GitHub Release target the reviewed commit and contain the expected artifacts and provenance.
4. Verify GitHub and Packagist identify `AGPL-3.0-or-later` for 1.1.1 while 1.0.x tags retain their original MIT terms.
5. Install the exact public package into clean plain PHP, Laravel, and Symfony fixtures:

```bash
composer require fleetbase/fleetbase-php:1.1.1
composer install --no-dev --optimize-autoloader
```

6. Confirm the public API reference renders the checked-in 1.1.1 PHP examples.

Never reuse, move, or rewrite a published tag. If validation fails after publication, publish a new patch release.
