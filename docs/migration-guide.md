# Migrating from 1.0.x to 1.1.x

This guide is being completed with the v1.1.0 implementation. It records changes that downstream consumers must assess before upgrading.

## License change

Version 1.1.0 changes the project license from MIT to `AGPL-3.0-or-later`. Versions 1.0.0 through 1.0.3 remain under the MIT license included in those tags. Review the AGPL terms and obtain your own legal advice about distribution, modification, and network use before adopting 1.1.0.

## PHP and Composer

The runtime constraint widens from `^7.4` to `^7.4 || ^8.0`. PHP 7.4 and 8.0 are tested only for backwards compatibility; use a PHP version that still receives upstream security fixes. Composer 2.2 or newer is required for development and verified consumer installs.

## Compatibility policy

The 1.1.0 release preserves the `Fleetbase\Sdk` namespace, facade constructor, existing store properties, `HttpClient` verbs and accessors, generic service methods, resource lifecycle/attribute methods, resource classes, and published order actions. Demonstrably unusable behavior is corrected with regression tests and changelog entries.

The final guide will list every corrected behavior, new exception type, transport injection option, and framework recipe before release. Fleetbase API v1 does not expose an SDK pagination contract, so 1.1.0 retains the legacy array return from `findAll()` and `query()` and does not introduce a speculative pagination method.

## Endpoint calls in 1.1.1

Version 1.1.1 adds an ergonomic form for every generated endpoint without removing the form published in 1.1.0. New code should pass URL identifiers positionally, followed by the request data and then optional transport options:

```php
$fleetbase->drivers->changeDriverPassword($driverId, $passwordData);
$fleetbase->orders->scheduleOrder($orderId, $scheduleData);
$fleetbase->orders->dispatchOrder($orderId);
```

The equivalent 1.1.0 envelope remains valid:

```php
$fleetbase->drivers->changeDriverPassword([
    'id' => $driverId,
    'body' => $passwordData,
], $requestOptions);
```

This is additive: existing positional arrays and PHP 8 named calls using `parameters:` and `options:` continue to work. The SDK translates both forms to the same HTTP request. It rejects ambiguous calls that provide the same body, query, or multipart data in both the direct data argument and request options.
