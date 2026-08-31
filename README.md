# Fleetbase PHP SDK

[![CI](https://github.com/fleetbase/fleetbase-php/actions/workflows/ci.yml/badge.svg?branch=main)](https://github.com/fleetbase/fleetbase-php/actions/workflows/ci.yml)
[![Coverage](https://codecov.io/gh/fleetbase/fleetbase-php/branch/main/graph/badge.svg)](https://codecov.io/gh/fleetbase/fleetbase-php)
[![Latest release](https://img.shields.io/packagist/v/fleetbase/fleetbase-php.svg)](https://packagist.org/packages/fleetbase/fleetbase-php)
[![PHP versions](https://img.shields.io/packagist/dependency-v/fleetbase/fleetbase-php/php)](https://packagist.org/packages/fleetbase/fleetbase-php)
[![Downloads](https://img.shields.io/packagist/dt/fleetbase/fleetbase-php.svg)](https://packagist.org/packages/fleetbase/fleetbase-php)
[![License: AGPL-3.0-or-later](https://img.shields.io/badge/license-AGPL--3.0--or--later-blue.svg)](LICENSE)

The official PHP client for the [Fleetbase API](https://fleetbase.io/docs/api). It supports Fleetbase Cloud and self-hosted Fleetbase installations while retaining the public API used by the 1.0.x SDK.

> License notice: the upcoming 1.1.0 release is licensed under `AGPL-3.0-or-later`. Published 1.0.x tags remain available under their original MIT license. Review [the migration guide](docs/migration-guide.md) before upgrading.

## Requirements

- PHP 7.4 or PHP 8.0–8.5
- Composer 2.2 or newer
- A Fleetbase API key

PHP 7.4 and 8.0 are compatibility targets only and no longer receive PHP security updates. Production applications should use a PHP version currently supported by the PHP project.

## Installation

```bash
composer require fleetbase/fleetbase-php
```

## Quick start

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use Fleetbase\Sdk\Fleetbase;

$fleetbase = new Fleetbase($_ENV['FLEETBASE_API_KEY']);

$place = $fleetbase->places->create([
    'name' => 'Space Needle',
    'street1' => '400 Broad Street',
    'city' => 'Seattle',
    'state' => 'WA',
    'country' => 'US',
]);

echo $place->id;
```

Never commit an API key. Load it from your runtime secret manager or environment.

## Self-hosted Fleetbase

```php
$fleetbase = new Fleetbase($_ENV['FLEETBASE_API_KEY'], [
    'host' => 'https://fleetbase.example.com',
    'namespace' => 'v1',
]);
```

The current modernization work is tracked in [the implementation plan](docs/MODERNIZATION_PLAN.md), the machine-checked [API contract manifest](contracts/postman-manifest.json), and the human-readable [220-request API coverage matrix](docs/api-coverage.md). Browse [all 220 generated PHP examples](docs/api-examples.md); CI executes each exact snippet against a hermetic transport. Isolated live-stack verification remains a release gate.

## Development

```bash
composer install
composer check
```

Tests are hermetic by default and must not use production credentials or mutate a shared API. See [CONTRIBUTING.md](CONTRIBUTING.md) for the quality, contract, and pull-request requirements.

## Security and support

Report vulnerabilities privately as described in [SECURITY.md](SECURITY.md). Usage questions and supported-version guidance are in [SUPPORT.md](SUPPORT.md).

## License

The code prepared for 1.1.0 and later is licensed under the [GNU Affero General Public License v3.0 or later](LICENSE). Previously published tags retain the license shipped with those tags.
