<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Fleetbase\Sdk\Fleetbase;
use GuzzleHttp\Psr7\HttpFactory;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpClient\Psr18Client;

$container = new ContainerBuilder();
$container->register('fleetbase.psr17_factory', HttpFactory::class);
$container->register('fleetbase.transport', Psr18Client::class)->setArguments([
    null,
    new Reference('fleetbase.psr17_factory'),
    new Reference('fleetbase.psr17_factory'),
]);
$container->register(Fleetbase::class, Fleetbase::class)
    ->setPublic(true)
    ->setArguments([
        'fixture_key',
        [
            'host' => 'https://fleetbase.example.test',
            'namespace' => 'api/v1',
            'httpClient' => new Reference('fleetbase.transport'),
        ],
    ]);
$container->compile();

$sdk = $container->get(Fleetbase::class);
if (!$sdk instanceof Fleetbase || !$sdk->getOptions()['httpClient'] instanceof Psr18Client) {
    throw new RuntimeException('The Symfony container did not inject its PSR-18 transport.');
}

fwrite(STDOUT, "Symfony container consumer verified.\n");
