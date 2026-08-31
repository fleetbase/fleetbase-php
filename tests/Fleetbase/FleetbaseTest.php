<?php

declare(strict_types=1);

namespace Fleetbase\Sdk\Test\Fleetbase;

use Fleetbase\Sdk\Fleetbase;
use Fleetbase\Sdk\Service;
use Fleetbase\Sdk\Services\OrderService;
use Fleetbase\Sdk\Test\TestCase;

final class FleetbaseTest extends TestCase
{
    public function testInitializesLegacyFacadeAndStores(): void
    {
        $sdk = new Fleetbase('test_public_key');

        $this->assertInstanceOf(OrderService::class, $sdk->orders);

        foreach (
            [
                'entities',
                'places',
                'drivers',
                'vehicles',
                'vendors',
                'contacts',
                'serviceAreas',
                'zones',
                'trackingStatuses',
                'serviceRates',
                'serviceQuotes',
            ] as $store
        ) {
            $this->assertInstanceOf(Service::class, $sdk->{$store});
        }
    }

    public function testPreservesConfigurationAndVersion(): void
    {
        $sdk = new Fleetbase('test_public_key', [
            'version' => 'v2',
            'host' => 'https://fleetbase.example.test/base',
            'namespace' => 'custom',
        ], true);

        $this->assertSame('v2', $sdk->getVersion());
        $this->assertSame([
            'version' => 'v2',
            'host' => 'https://fleetbase.example.test/base',
            'namespace' => 'custom',
            'debug' => true,
            'publicKey' => 'test_public_key',
        ], $sdk->getOptions());
    }

    public function testNewInstanceKeepsLegacyVariadicFactory(): void
    {
        $sdk = Fleetbase::newInstance('test_public_key', ['namespace' => 'api']);

        $this->assertSame('api', $sdk->getOptions()['namespace']);
    }
}
