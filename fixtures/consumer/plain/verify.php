<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Fleetbase\Sdk\Fleetbase;
use Fleetbase\Sdk\Services\OrderService;

$sdk = new Fleetbase('fixture_key', [
    'host' => 'https://fleetbase.example.test',
    'namespace' => 'api/v1',
]);

if (!$sdk->orders() instanceof OrderService || $sdk->getVersion() !== 'v1') {
    throw new RuntimeException('The plain PHP consumer could not resolve the SDK facade.');
}

fwrite(STDOUT, "Plain PHP consumer verified.\n");
