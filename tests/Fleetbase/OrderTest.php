<?php

declare(strict_types=1);

namespace Fleetbase\Sdk\Test\Fleetbase;

use Fleetbase\Sdk\Fleetbase;
use Fleetbase\Sdk\Resources\Order;
use Fleetbase\Sdk\Test\TestCase;

final class OrderTest extends TestCase
{
    const TEST_RESOURCE_ID = 'order_123';

    public function testIsCreatable()
    {
        $publicKey = $_ENV['FLEETBASE_KEY'];
        $fleetbase = new Fleetbase($publicKey);

        $resource = $fleetbase->orders->create([
            'internal_id' => 'SDKTEST123',
            'payload' => [
                'pickup' => '9 Raffles Place, Republic Plaza, Singapore',
                'dropoff' => '2 Orchard Turn, Singapore 238801'
            ]
        ]);

        $this->assertInstanceOf(Order::class, $resource);
    }

    public function testIsListable()
    {
        $publicKey = $_ENV['FLEETBASE_KEY'];
        $fleetbase = new Fleetbase($publicKey);

        $resources = $fleetbase->orders->findAll();

        $this->assertIsArray($resources);
        $this->assertInstanceOf(Order::class, $resources[0]);
    }

    public function testIsRetrievable()
    {
        $publicKey = $_ENV['FLEETBASE_KEY'];
        $fleetbase = new Fleetbase($publicKey);

        $resource = $fleetbase->orders->findRecord(self::TEST_RESOURCE_ID);

        $this->assertInstanceOf(Order::class, $resource);
    }

    public function testIsSaveable()
    {
        $publicKey = $_ENV['FLEETBASE_KEY'];
        $fleetbase = new Fleetbase($publicKey);

        $resource = $fleetbase->orders->findRecord(self::TEST_RESOURCE_ID);
        $resource->save();

        $this->assertInstanceOf(Order::class, $resource);
    }

    public function testIsUpdatable()
    {
        $publicKey = $_ENV['FLEETBASE_KEY'];
        $fleetbase = new Fleetbase($publicKey);

        $newInternalId = 'SDKTEST1232';

        $resource = $fleetbase->orders->findRecord(self::TEST_RESOURCE_ID);
        $resource->update(['internal_id' => $newInternalId]);

        $this->assertInstanceOf(Order::class, $resource);
        $this->assertEquals($newInternalId, $resource->getAttribute('internal_id'));
    }

    public function testIsDeletable()
    {
        $publicKey = $_ENV['FLEETBASE_KEY'];
        $fleetbase = new Fleetbase($publicKey);

        $resource = $fleetbase->orders->findRecord(self::TEST_RESOURCE_ID);
        $resource->destroy();

        $this->assertIsObject($resource);
        $this->assertTrue($resource->getAttribute('deleted'));
        $this->assertTrue($resource->isDestroyed);
        $this->assertEquals(self::TEST_RESOURCE_ID, $resource->id);
    }
}
