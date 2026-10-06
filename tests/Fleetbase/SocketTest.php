<?php

declare(strict_types=1);

namespace Fleetbase\Sdk\Test\Fleetbase;

use Fleetbase\Sdk\Exception\NotFoundException;
use Fleetbase\Sdk\Services\SocketService;
use Fleetbase\Sdk\Test\TestCase;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;

final class SocketTest extends TestCase
{
    public function testMintsSocketTokenWithPostToSocketToken(): void
    {
        $service = new SocketService($this->mockHttpClient([
            new Response(200, ['Content-Type' => 'application/json'], '{"token":"header.payload.signature","expires_in":900,"expires_at":"2026-01-01T00:15:00+00:00"}'),
        ]));

        $minted = $service->token();

        self::assertIsObject($minted);
        $attributes = get_object_vars($minted);
        self::assertSame('header.payload.signature', $attributes['token'] ?? null);
        self::assertSame(900, $attributes['expires_in'] ?? null);
        self::assertSame('2026-01-01T00:15:00+00:00', $attributes['expires_at'] ?? null);

        self::assertCount(1, $this->history);
        $transaction = $this->history[0];
        self::assertIsArray($transaction);
        $request = $transaction['request'] ?? null;
        self::assertInstanceOf(RequestInterface::class, $request);
        self::assertSame('POST', $request->getMethod());
        self::assertSame('/v1/socket/token', $request->getUri()->getPath());
    }

    public function testServerWithoutSocketAuthRaisesNotFound(): void
    {
        $service = new SocketService($this->mockHttpClient([
            new Response(404, ['Content-Type' => 'application/json'], '{"error":"Not found"}'),
        ]));

        $this->expectException(NotFoundException::class);
        $service->token();
    }
}
