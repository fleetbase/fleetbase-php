<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Fleetbase\Sdk\Fleetbase;
use Illuminate\Container\Container;

$container = new Container();
$container->singleton(Fleetbase::class, static function (): Fleetbase {
    return new Fleetbase('fixture_key', [
        'host' => 'https://fleetbase.example.test',
        'namespace' => 'api/v1',
    ]);
});

$sdk = $container->make(Fleetbase::class);
if (!$sdk instanceof Fleetbase || $container->make(Fleetbase::class) !== $sdk) {
    throw new RuntimeException('The Laravel container did not resolve the Fleetbase singleton.');
}

fwrite(STDOUT, "Laravel container consumer verified.\n");
