# Fleetbase PHP SDK

[![CI](https://github.com/fleetbase/fleetbase-php/actions/workflows/ci.yml/badge.svg)](https://github.com/fleetbase/fleetbase-php/actions/workflows/ci.yml)
[![Security](https://github.com/fleetbase/fleetbase-php/actions/workflows/security.yml/badge.svg)](https://github.com/fleetbase/fleetbase-php/actions/workflows/security.yml)
[![Coverage: 100% lines and branches](https://img.shields.io/badge/coverage-100%25_lines_%26_branches-brightgreen)](https://github.com/fleetbase/fleetbase-php/actions/workflows/ci.yml)
[![Latest release](https://img.shields.io/packagist/v/fleetbase/fleetbase-php.svg)](https://packagist.org/packages/fleetbase/fleetbase-php)
[![PHP versions](https://img.shields.io/packagist/dependency-v/fleetbase/fleetbase-php/php)](https://packagist.org/packages/fleetbase/fleetbase-php)
[![Downloads](https://img.shields.io/packagist/dt/fleetbase/fleetbase-php.svg)](https://packagist.org/packages/fleetbase/fleetbase-php)
[![License: AGPL-3.0-or-later](https://img.shields.io/badge/license-AGPL--3.0--or--later-blue.svg)](LICENSE)

The official PHP client for the [Fleetbase API](https://fleetbase.io/docs/api). It supports Fleetbase Cloud and self-hosted installations, offers explicit methods for all 220 locked Fleetbase and Core API requests, and retains the public API used by the 1.0.x SDK.

> The upcoming 1.1.0 release changes the license to `AGPL-3.0-or-later`. Published 1.0.x tags remain under the MIT license shipped with those releases. Review the [migration guide](docs/migration-guide.md) before upgrading.

Maintainers preparing 1.1.0 should use the [release checklist](docs/release-checklist.md) and [default-branch migration runbook](docs/default-branch-migration.md).

## Requirements

- PHP 7.4 or PHP 8.0–8.5
- Composer 2.2 or newer
- A Fleetbase API key

PHP 7.4 and 8.0 are compatibility targets but no longer receive PHP security fixes. Use a currently supported PHP version in production.

## Installation

```bash
composer require fleetbase/fleetbase-php
```

Composer installs Guzzle as the default transport. Applications may instead inject any PSR-18 client and compatible PSR-17 request and stream factories.

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

Existing property access remains supported (`$fleetbase->orders`). Explicit accessors such as `$fleetbase->orders()` are available for static analysis and dependency injection. Browse [all 220 generated PHP examples](docs/api-examples.md); CI executes each exact snippet against a hermetic transport.

## Configuration

The second constructor argument accepts client configuration. The third legacy argument retains the debug flag without printing requests or credentials.

```php
$fleetbase = new Fleetbase($_ENV['FLEETBASE_API_KEY'], [
    'host' => 'https://api.fleetbase.io',
    'namespace' => 'v1',
    'version' => 'v1',
    'max_retries' => 2,
    'retry_delay_ms' => 200,
], false);
```

Supported transport configuration includes `httpClient` (or legacy `client`), `requestFactory`, and `streamFactory`. Per-request options include `headers`, `timeout`, `connect_timeout`, `allow_redirects`, TLS `verify`, `proxy`, `idempotency_key`, `max_retries`, `retry_delay_ms`, `multipart`, `form_params`, and a raw string `body`.

### Self-hosted Fleetbase

The host may include a path prefix, and the namespace may contain multiple segments. Slashes are normalized without discarding the prefix.

```php
$fleetbase = new Fleetbase($_ENV['FLEETBASE_API_KEY'], [
    'host' => 'https://fleetbase.example.com/platform',
    'namespace' => 'api/v1',
]);
```

### Injecting a PSR-18 transport

```php
use Fleetbase\Sdk\Fleetbase;
use GuzzleHttp\Psr7\HttpFactory;
use Symfony\Component\HttpClient\Psr18Client;

$factory = new HttpFactory();
$transport = new Psr18Client(null, $factory, $factory);

$fleetbase = new Fleetbase($_ENV['FLEETBASE_API_KEY'], [
    'httpClient' => $transport,
    'requestFactory' => $factory,
    'streamFactory' => $factory,
]);
```

## Services and responses

Standard resource services retain `create()`, `update()`, `findRecord()`, `findAll()`, `query()`, `queryRecord()`, and `destroy()`. Dedicated actions use discoverable methods named after the official Postman request.

```php
$order = $fleetbase->orders->dispatchOrder([
    'id' => 'order_123',
]);

$response = $fleetbase->client->request('GET', 'future-endpoint', [
    'limit' => 10,
]);
```

The raw `request()` method is the forward-compatibility escape hatch. It still applies authentication, URL normalization, decoding, retries, hooks, and exception mapping.

## Errors

All SDK failures are catchable as `FleetbaseException`. More specific exceptions cover authentication, authorization, validation, missing resources, conflicts, rate limits, server responses, timeouts, transport failures, decoding failures, and unexpected responses.

```php
use Fleetbase\Sdk\Exception\ValidationException;
use Fleetbase\Sdk\FleetbaseException;

try {
    $fleetbase->places->create($attributes);
} catch (ValidationException $exception) {
    foreach ($exception->getDetails() as $field => $messages) {
        // Present validation feedback to the caller.
    }
} catch (FleetbaseException $exception) {
    error_log(sprintf(
        'Fleetbase request %s failed with HTTP %s (request %s)',
        $exception->getRequestMethod() ?? 'unknown',
        $exception->getStatusCode() ?? 'none',
        $exception->getRequestId() ?? 'unavailable'
    ));
}
```

Exception URLs are sanitized and never include credentials or query strings. Avoid logging request bodies because they may contain customer or authentication data.

## Uploads and downloads

Multipart actions accept standard Guzzle multipart parts. The Core file service also exposes the official base64 upload action.

```php
$file = $fleetbase->files->uploadFile([], [
    'multipart' => [
        [
            'name' => 'file',
            'contents' => file_get_contents('/path/to/document.pdf'),
            'filename' => 'document.pdf',
        ],
        ['name' => 'path', 'contents' => 'documents'],
    ],
]);

$contents = $fleetbase->files->downloadFile([
    'id' => 'file_123',
]);
```

Non-JSON successful responses are returned as strings. The underlying PSR-7 response is available from `$fleetbase->client->getLastPsrResponse()` when headers or streaming behavior are needed.

## Retries and idempotency

Retries are opt-in. GET, HEAD, OPTIONS, PUT, and DELETE requests may retry on transport errors, HTTP 429, and HTTP 5xx. A POST or PATCH retries only when it carries an idempotency key. `Retry-After` is honored; otherwise delays use capped exponential backoff.

```php
$order = $fleetbase->orders->createOrder(
    ['body' => $attributes],
    [
        'idempotency_key' => $operationId,
        'max_retries' => 2,
        'retry_delay_ms' => 250,
    ]
);
```

## Request diagnostics

Use per-request hooks for application-level tracing without exposing credentials.

```php
$result = $fleetbase->orders->queryOrders([], [
    'onBefore' => static function (): void {
        // Start a timer or trace span.
    },
    'onAfter' => static function ($decoded): void {
        // Close the trace span.
    },
]);

$status = $fleetbase->client->getLastResponse()->getStatusCode();
```

The compatibility `debug` flag is retained as configuration state but deliberately does not dump request bodies or API keys.

## Framework integration

The SDK has no required framework dependency.

### Laravel container

```php
use Fleetbase\Sdk\Fleetbase;
use Illuminate\Contracts\Container\Container;

$app->singleton(Fleetbase::class, static function (Container $app): Fleetbase {
    return new Fleetbase((string) config('services.fleetbase.key'), [
        'host' => (string) config('services.fleetbase.host'),
        'namespace' => (string) config('services.fleetbase.namespace', 'v1'),
    ]);
});
```

### Symfony services

```yaml
services:
  Fleetbase\Sdk\Fleetbase:
    arguments:
      $publicKey: '%env(FLEETBASE_API_KEY)%'
      $config:
        host: '%env(resolve:FLEETBASE_API_HOST)%'
        namespace: 'v1'
```

The repository’s clean consumer fixtures verify plain PHP, Laravel 12 container resolution, Symfony 7.4 dependency injection with `Psr18Client`, and optimized `--no-dev` autoloading.

## Compatibility and migration

Version 1.1.0 preserves the documented public/protected surface of both 1.0.2 and 1.0.3 while correcting unusable behavior. See the [migration guide](docs/migration-guide.md) for the license change, PHP constraints, transport behavior, and corrected edge cases.

The [API coverage matrix](docs/api-coverage.md) maps every request to its source, SDK method, fixture, and deterministic test. Contract sources are locked in [contracts/contract-lock.json](contracts/contract-lock.json).

## Development

```bash
composer install
composer check
composer test:coverage
```

Tests are hermetic by default and must not use production credentials or mutate a shared API. CI enforces PHP 7.4–8.5 with lowest/latest dependencies, PHPStan at max level, generated-contract drift, compatibility snapshots, exact 100% line and branch coverage, consumer fixtures, security checks, and archive inspection. See [CONTRIBUTING.md](CONTRIBUTING.md) for contribution requirements.

## Security and support

Report vulnerabilities privately as described in [SECURITY.md](SECURITY.md). Usage questions and supported-version guidance are in [SUPPORT.md](SUPPORT.md).

## License

Code prepared for 1.1.0 and later is licensed under the [GNU Affero General Public License v3.0 or later](LICENSE). Previously published tags retain the license shipped with those tags.
