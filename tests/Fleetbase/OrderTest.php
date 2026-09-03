<?php

declare(strict_types=1);

namespace Fleetbase\Sdk\Test\Fleetbase;

use Fleetbase\Sdk\Resources\Order;
use Fleetbase\Sdk\Service;
use Fleetbase\Sdk\Services\OrderService;
use Fleetbase\Sdk\Test\TestCase;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;

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

    public function testLegacyOrderServiceAndResourceActionsResolveCorrectPaths(): void
    {
        $client = $this->mockHttpClient(array_fill(0, 25, new Response(200, ['Content-Type' => 'application/json'], '{}')));
        $service = new OrderService($client);
        $before = false;

        $service->getDistanceAndTime('order_1', ['include' => 'route']);
        $service->getDistanceAndTime('order_1', ['onBefore' => function () use (&$before): void {
            $before = true;
        }]);
        $service->getNextActivity('order_1');
        $service->dispatch('order_1');
        $service->dispatchOrder('order_1');
        $service->dispatchOrder(['id' => 'order_1']);
        $service->start('order_1');
        $service->updateActivity('order_1');
        $service->setDestination('order_1', 'destination with space');
        $service->captureQrCode('order_1');
        $service->captureQrCode('order_1', 'subject with space');
        $service->captureSignature('order_1');
        $service->captureSignature('order_1', 'subject with space');
        $service->complete('order_1');
        $service->cancel('order_1');

        $order = new Order(['id' => 'order_2'], $service);
        $order->__constructor(['id' => 'order_2'], $service);
        $order->getDistanceAndTime();
        $order->getNextActivity();
        $order->dispatch();
        $order->start();
        $order->updateActivity();
        $order->setDestination('destination_2');
        $order->captureQrCode('subject_2');
        $order->captureSignature('subject_2');
        $order->complete();
        $order->cancel();

        self::assertTrue($before);
        self::assertCount(25, $this->history);
        $paths = array_map(function ($transaction): string {
            self::assertIsArray($transaction);
            $request = $transaction['request'] ?? null;
            self::assertInstanceOf(RequestInterface::class, $request);
            return $request->getMethod() . ' ' . $request->getUri()->getPath()
                . ($request->getUri()->getQuery() !== '' ? '?' . $request->getUri()->getQuery() : '');
        }, $this->history);
        self::assertContains('GET /v1/orders/order_1/distance-and-time?include=route', $paths);
        self::assertSame(3, count(array_filter($paths, static function (string $path): bool {
            return $path === 'PATCH /v1/orders/order_1/dispatch';
        })));
        self::assertContains('PATCH /v1/orders/order_2/dispatch', $paths);
        self::assertContains('PATCH /v1/orders/order_1/set-destination/destination%20with%20space', $paths);
        self::assertContains('POST /v1/orders/order_1/capture-qr', $paths);
        self::assertContains('POST /v1/orders/order_1/capture-qr/subject%20with%20space', $paths);
        self::assertContains('POST /v1/orders/order_1/capture-signature', $paths);
        self::assertContains('DELETE /v1/orders/order_2/cancel', $paths);
    }

    public function testOrderActionsValidateLegacyArgumentsAndAttachment(): void
    {
        $service = new OrderService($this->mockHttpClient([]));
        foreach ([['invalid', []], [[], 'invalid']] as $arguments) {
            try {
                (new \ReflectionMethod($service, 'getDistanceAndTime'))->invoke($service, 'order_1', $arguments[0], $arguments[1]);
                self::fail('Expected invalid action arguments.');
            } catch (\InvalidArgumentException $exception) {
                self::assertStringContainsString('arrays', $exception->getMessage());
            }

            try {
                (new \ReflectionMethod($service, 'dispatch'))->invoke($service, 'order_1', $arguments[0], $arguments[1]);
                self::fail('Expected invalid dispatch arguments.');
            } catch (\InvalidArgumentException $exception) {
                self::assertStringContainsString('arrays', $exception->getMessage());
            }
        }

        foreach ([new Order(), new Order(['id' => 'order_1'], new Service('Order', $this->mockHttpClient([])))] as $order) {
            try {
                $order->cancel();
                self::fail('Expected an invalid order attachment.');
            } catch (\LogicException $exception) {
                self::assertStringContainsString($order->getService() === null ? 'OrderService' : 'OrderService', $exception->getMessage());
            }
        }

        try {
            (new Order([], $service))->cancel();
            self::fail('Expected a missing order ID.');
        } catch (\LogicException $exception) {
            self::assertStringContainsString('ID', $exception->getMessage());
        }
    }
}
