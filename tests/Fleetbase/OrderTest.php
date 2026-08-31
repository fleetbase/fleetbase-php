<?php

declare(strict_types=1);

namespace Fleetbase\Sdk\Test\Fleetbase;

use Fleetbase\Sdk\Resources\Order;
use Fleetbase\Sdk\Services\OrderService;
use Fleetbase\Sdk\Test\TestCase;

final class OrderTest extends TestCase
{
    public function testPublishedOrderResourceActionSurfaceRemainsCallable(): void
    {
        foreach (
            [
                'getDistanceAndTime',
                'getNextActivity',
                'dispatch',
                'start',
                'updateActivity',
                'setDestination',
                'captureQrCode',
                'captureSignature',
                'complete',
                'cancel',
            ] as $method
        ) {
            $this->assertTrue(method_exists(Order::class, $method), $method . ' is missing from Order');
            $this->assertTrue(method_exists(OrderService::class, $method), $method . ' is missing from OrderService');
        }
    }
}
