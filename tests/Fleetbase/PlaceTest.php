<?php

declare(strict_types=1);

namespace Fleetbase\Sdk\Test\Fleetbase;

use Fleetbase\Sdk\Resources\Place;
use Fleetbase\Sdk\Service;
use Fleetbase\Sdk\Test\TestCase;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;

final class PlaceTest extends TestCase
{
    private const TEST_RESOURCE_ID = 'place_123';

    public function testGenericServiceCrudAndQueryContract(): void
    {
        $client = $this->mockHttpClient([
            $this->jsonResponse(['id' => self::TEST_RESOURCE_ID, 'name' => 'HQ']),
            $this->jsonResponse(['id' => self::TEST_RESOURCE_ID, 'name' => 'HQ']),
            $this->jsonResponse([
                ['id' => self::TEST_RESOURCE_ID],
                ['id' => 'place_456'],
            ]),
            $this->jsonResponse([['id' => self::TEST_RESOURCE_ID]]),
            $this->jsonResponse(['id' => self::TEST_RESOURCE_ID]),
            $this->jsonResponse(['id' => self::TEST_RESOURCE_ID, 'name' => 'Updated']),
            $this->jsonResponse(['id' => self::TEST_RESOURCE_ID, 'deleted' => true]),
        ]);
        $service = new Service('Place', $client);

        $created = $service->create(['name' => 'HQ']);
        $found = $service->findRecord(self::TEST_RESOURCE_ID);
        $all = $service->findAll();
        $queried = $service->query(['page' => 2]);
        $single = $service->queryRecord(['name' => 'HQ']);
        $updated = $service->update(self::TEST_RESOURCE_ID, ['name' => 'Updated']);
        $destroyed = $service->destroy(self::TEST_RESOURCE_ID);

        $this->assertInstanceOf(Place::class, $created);
        $this->assertInstanceOf(Place::class, $found);
        $this->assertIsArray($all);
        $this->assertIsArray($queried);
        $this->assertContainsOnlyInstancesOf(Place::class, $all);
        $this->assertContainsOnlyInstancesOf(Place::class, $queried);
        $this->assertInstanceOf(Place::class, $single);
        $this->assertInstanceOf(Place::class, $updated);
        $this->assertInstanceOf(Place::class, $destroyed);
        $this->assertSame('Updated', $updated->getAttribute('name'));
        $this->assertTrue($destroyed->getAttribute('deleted'));

        $requests = [];
        foreach ($this->history as $transaction) {
            if (!is_array($transaction) || !($transaction['request'] ?? null) instanceof RequestInterface) {
                self::fail('Guzzle history contained an invalid transaction.');
            }
            $request = $transaction['request'];
            $requests[] = $request->getMethod() . ' ' . $request->getUri()->getPath()
                . ($request->getUri()->getQuery() !== '' ? '?' . $request->getUri()->getQuery() : '');
        }

        $this->assertSame([
            'POST /v1/places',
            'GET /v1/places/place_123',
            'GET /v1/places',
            'GET /v1/places?page=2',
            'GET /v1/places?name=HQ&single=1',
            'PUT /v1/places/place_123',
            'DELETE /v1/places/place_123',
        ], $requests);
    }

    /** @param mixed $data */
    private function jsonResponse($data, int $status = 200): Response
    {
        return new Response($status, ['Content-Type' => 'application/json'], json_encode($data, JSON_THROW_ON_ERROR));
    }
}
