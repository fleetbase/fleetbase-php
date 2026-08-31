# Contributing

Thank you for improving the Fleetbase PHP SDK.

## Setup

```bash
git clone https://github.com/fleetbase/fleetbase-php.git
cd fleetbase-php
composer install
composer check
```

Use a currently supported PHP release for development. Compatibility with PHP 7.4 through 8.5 is enforced in CI.

## Change requirements

- Preserve the public API snapshots in `contracts/` unless the change is explicitly classified and approved.
- Trace endpoint changes to the locked Postman, Core API, or Fleet-Ops source and update the contract manifest.
- Add deterministic success, error, and boundary tests. Unit tests must not call a shared or production API.
- Keep line and branch coverage at 100%; meaningful assertions and mutation results remain review gates.
- Run `composer check` and include the commands and results in the pull request.
- Update README, API coverage, changelog, and migration guidance when behavior or usage changes.
- Never commit credentials, `.env` files, caches, generated coverage, or `vendor/`.

Integration tests use a disposable Fleetbase stack and explicit opt-in workflow. Do not substitute personal or production API keys.

## Pull requests

Keep changes bounded and explain the contract source, compatibility classification, tests, coverage, documentation effect, security effect, and known limitations. By contributing, you agree that your contribution is licensed under `AGPL-3.0-or-later` for releases carrying that license.

Community participation is governed by [CODE_OF_CONDUCT.md](CODE_OF_CONDUCT.md). Report security issues through [SECURITY.md](SECURITY.md), not a public pull request.
