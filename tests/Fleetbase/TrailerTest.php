<?php

declare(strict_types=1);

namespace Fleetbase\Sdk\Test\Fleetbase;

use Fleetbase\Sdk\Exception\ConflictException;
use Fleetbase\Sdk\Resources\Trailer;
use Fleetbase\Sdk\Services\TrailerService;
use Fleetbase\Sdk\Test\TestCase;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;

final class TrailerTest extends TestCase
{
    public function testTrailerResourceHydrationAndActiveRecordSaveUseTrailerRoutes(): void
    {
        $client = $this->mockHttpClient([
            new Response(201, [], '{"id":"trailer_test","name":"Reefer","attachment_state":"detached","equipment":[]}'),
            new Response(200, [], '{"id":"trailer_test","name":"Updated reefer"}'),
        ]);
        $service = new TrailerService($client);
        $trailer = $service->create(['name' => 'Reefer', 'type' => 'reefer']);
        self::assertInstanceOf(Trailer::class, $trailer);
        self::assertSame('detached', $trailer->getAttribute('attachment_state'));
        self::assertSame([], $trailer->getAttribute('equipment'));
        $trailer->setAttribute('name', 'Updated reefer');
        $trailer->save();

        foreach ($this->history as $index => $transaction) {
            self::assertIsArray($transaction);
            $request = $transaction['request'] ?? null;
            self::assertInstanceOf(RequestInterface::class, $request);
            self::assertSame($index === 0 ? 'POST' : 'PUT', $request->getMethod());
            self::assertSame($index === 0 ? '/v1/trailers' : '/v1/trailers/trailer_test', $request->getUri()->getPath());
            $body = json_decode((string) $request->getBody(), true, 512, JSON_THROW_ON_ERROR);
            self::assertIsArray($body);
            self::assertSame($index === 0 ? 'Reefer' : 'Updated reefer', $body['name'] ?? null);
        }
    }

    public function testAttachmentConflictPreservesTheApiError(): void
    {
        $service = new TrailerService($this->mockHttpClient([
            new Response(409, [], '{"error":"Trailer is already attached to another vehicle. Detach it before moving."}'),
        ]));
        $this->expectException(ConflictException::class);
        $this->expectExceptionMessage('Trailer is already attached');
        $service->attachTrailerToVehicle('trailer_test', ['vehicle' => 'vehicle_other']);
    }
}
