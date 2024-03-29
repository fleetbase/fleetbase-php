# Fleetbase PHP SDK

[![Source Code][badge-source]][source]
[![Latest Version][badge-release]][packagist]
[![Software License][badge-license]][license]
[![PHP Version][badge-php]][php]
[![Build Status][badge-build]][build]
[![Coverage Status][badge-coverage]][coverage]
[![Total Downloads][badge-downloads]][downloads]

Fleetbase PHP SDK

This project adheres to a [Contributor Code of Conduct][conduct]. By
participating in this project and its community, you are expected to uphold this
code.


## Requirements

PHP 7.4 and later.


## Installation

The preferred method of installation is via [Composer][]. Run the following
command to install the package and add it as a requirement to your project's
`composer.json`:

```bash
composer require fleetbase/fleetbase-php
```


## Quick Start

Simple usage looks like:

```php
$fleetbase = new \Fleetbase\Sdk\Fleetbase('< api key here >');

$spaceNeedle = $fleetbase->places->create([
    'name' => 'Space Needle',
    'street1' => '400 Broad Street',
    'city' => 'Seattle',
    'state' => 'WA',
    'country' => 'US'
]);
```

## Documentation

Check out the [documentation website][documentation] for detailed information
and code examples.


## Contributing

Contributions are welcome! Please read [CONTRIBUTING][] for details.


## Copyright and License

The fleetbase/fleetbase-php library is copyright © [Fleetbase Pte Ltd.](https://fleetbase.io)
and licensed for use under the MIT License (MIT). Please see [LICENSE][] for
more information.


[conduct]: https://github.com/fleetbase/fleetbase-php/blob/master/.github/CODE_OF_CONDUCT.md
[composer]: http://getcomposer.org/
[documentation]: https://fleetbase.github.io/fleetbase-php/
[contributing]: https://github.com/fleetbase/fleetbase-php/blob/master/.github/CONTRIBUTING.md

[badge-source]: http://img.shields.io/badge/source-fleetbase/fleetbase--php-blue.svg?style=flat-square
[badge-release]: https://img.shields.io/packagist/v/fleetbase/fleetbase-php.svg?style=flat-square&label=release
[badge-license]: https://img.shields.io/packagist/l/fleetbase/fleetbase-php.svg?style=flat-square
[badge-php]: https://img.shields.io/packagist/php-v/fleetbase/fleetbase-php.svg?style=flat-square
[badge-build]: https://img.shields.io/travis/fleetbase/fleetbase-php/master.svg?style=flat-square
[badge-coverage]: https://img.shields.io/coveralls/github/fleetbase/fleetbase-php/master.svg?style=flat-square
[badge-downloads]: https://img.shields.io/packagist/dt/fleetbase/fleetbase-php.svg?style=flat-square&colorB=mediumvioletred

[source]: https://github.com/fleetbase/fleetbase-php
[packagist]: https://packagist.org/packages/fleetbase/fleetbase-php
[license]: https://github.com/fleetbase/fleetbase-php/blob/master/LICENSE
[php]: https://php.net
[build]: https://travis-ci.org/fleetbase/fleetbase-php
[coverage]: https://coveralls.io/r/fleetbase/fleetbase-php?branch=master
[downloads]: https://packagist.org/packages/fleetbase/fleetbase-php
