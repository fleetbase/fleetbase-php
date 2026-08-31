# Migrating from 1.0.x to 1.1.0

This guide is being completed with the v1.1.0 implementation. It records changes that downstream consumers must assess before upgrading.

## License change

Version 1.1.0 changes the project license from MIT to `AGPL-3.0-or-later`. Versions 1.0.0 through 1.0.3 remain under the MIT license included in those tags. Review the AGPL terms and obtain your own legal advice about distribution, modification, and network use before adopting 1.1.0.

## PHP and Composer

The runtime constraint widens from `^7.4` to `^7.4 || ^8.0`. PHP 7.4 and 8.0 are tested only for backwards compatibility; use a PHP version that still receives upstream security fixes. Composer 2.2 or newer is required for development and verified consumer installs.

## Compatibility policy

The 1.1.0 release preserves the `Fleetbase\Sdk` namespace, facade constructor, existing store properties, `HttpClient` verbs and accessors, generic service methods, resource lifecycle/attribute methods, resource classes, and published order actions. Demonstrably unusable behavior is corrected with regression tests and changelog entries.

The final guide will list every corrected behavior, new exception type, pagination/return-shape addition, transport injection option, and framework recipe before release.
