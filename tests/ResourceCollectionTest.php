<?php

declare(strict_types=1);

namespace Fleetbase\Sdk\Test;

use Fleetbase\Sdk\Collection;
use Fleetbase\Sdk\Resource;
use Fleetbase\Sdk\Resources\Place;
use Fleetbase\Sdk\Service;
use GuzzleHttp\Psr7\Response;
use JsonSerializable;

final class ResourceCollectionTest extends TestCase
{
    public function testResourceLifecycleTracksChangesHooksAndState(): void
    {
        $client = $this->mockHttpClient([
            $this->jsonResponse(['id' => 'place_1', 'name' => 'Created']),
            $this->jsonResponse(['id' => 'place_1', 'name' => 'Saved']),
            $this->jsonResponse(['id' => 'place_1', 'name' => 'Reloaded']),
            $this->jsonResponse([]),
        ]);
        $service = new Service('Place', $client);
        $resource = new Place([], $service);
        $states = [];
        $consumerResponse = null;

        $created = $resource->create(['name' => 'Created'], [
            'onBefore' => function () use ($resource, &$states): void {
                $states[] = $resource->__get('isSaving');
            },
            'onAfter' => function ($response) use (&$consumerResponse): void {
                $consumerResponse = $response;
            },
        ]);
        self::assertInstanceOf(Place::class, $created);
        self::assertSame('place_1', $resource->getAttribute('id'));
        self::assertSame([true], $states);
        self::assertIsObject($consumerResponse);
        self::assertFalse($resource->__get('isSaving'));

        $resource->__set('name', 'Changed');
        self::assertTrue(isset($resource->name));
        self::assertTrue($resource->isDirty(null));
        self::assertTrue($resource->isDirty('name'));
        self::assertTrue($resource->isDirty(['missing', 'name']));
        self::assertSame(['name' => 'Changed'], $resource->getDirtyAttributes());
        self::assertSame(['old' => 'Created', 'new' => 'Changed'], $resource->getChanges()['name']);

        $saved = $resource->save();
        self::assertInstanceOf(Place::class, $saved);
        self::assertSame('Saved', $resource->__get('name'));
        self::assertFalse($resource->isDirty(null));
        self::assertSame($resource, $resource->reload());
        self::assertSame('Reloaded', $resource->__get('name'));
        self::assertFalse($resource->__get('isReloading'));

        $destroyed = $resource->destroy();
        self::assertInstanceOf(Place::class, $destroyed);
        self::assertTrue($resource->__get('isDestroyed'));
        self::assertSame('place_1', $resource->__get('id'));
        self::assertFalse($resource->__get('isDestroying'));
        self::assertSame($service, $resource->getService());

        $updateTransaction = $this->history[1] ?? null;
        self::assertIsArray($updateTransaction);
        $updateRequest = $updateTransaction['request'] ?? null;
        self::assertInstanceOf(\Psr\Http\Message\RequestInterface::class, $updateRequest);
        self::assertSame('{"name":"Changed"}', (string) $updateRequest->getBody());
    }

    public function testResourceAttributeAndSerializationBoundaries(): void
    {
        $nested = new Resource(['id' => 'nested']);
        $serializable = new class () implements JsonSerializable {
            /** @return array<string, string> */
            public function jsonSerialize(): array
            {
                return ['serialized' => 'yes'];
            }
        };
        $resource = new Resource([
            'id' => 'resource_1',
            'empty' => '',
            'null' => null,
            'nested' => $nested,
            'list' => [$nested, $serializable],
        ]);

        self::assertSame('resource_1', $resource->getAttribute('id'));
        self::assertSame(['id' => 'resource_1'], $resource->getAttribute(['id']));
        self::assertSame('default', (new \ReflectionMethod($resource, 'getAttribute'))->invoke($resource, 123, 'default'));
        self::assertTrue($resource->hasAttribute(['id', 'empty']));
        self::assertFalse((new \ReflectionMethod($resource, 'hasAttribute'))->invoke($resource, ['id', 2]));
        self::assertTrue($resource->isAttributeFilled('id'));
        self::assertFalse($resource->isAttributeFilled(['id', 'empty']));
        self::assertFalse($resource->isAttributeFilled('missing'));
        self::assertSame(['id' => 'resource_1'], $resource->getAttributes(['id']));
        self::assertSame($resource->getAttributes(), $resource->getAttributes(null));
        self::assertSame([
            'id' => 'resource_1',
            'empty' => '',
            'null' => null,
            'nested' => ['id' => 'nested'],
            'list' => [['id' => 'nested'], ['serialized' => 'yes']],
        ], $resource->toArray());
        self::assertSame($resource->toArray(), $resource->jsonSerialize());

        self::assertTrue($resource->offsetExists('id'));
        self::assertFalse($resource->offsetExists(0));
        self::assertSame('resource_1', $resource->offsetGet('id'));
        self::assertNull($resource->offsetGet(0));
        $resource->offsetSet('new', 'value');
        self::assertSame('value', $resource->getAttribute('new'));
        $resource->offsetUnset('new');
        self::assertFalse(isset($resource->new));
        $resource->offsetUnset(0);

        try {
            $resource->offsetSet('', 'invalid');
            self::fail('Expected an invalid resource offset.');
        } catch (\InvalidArgumentException $exception) {
            self::assertStringContainsString('non-empty', $exception->getMessage());
        }
    }

    public function testResourceRequiresAnAttachedServiceAndIdentifier(): void
    {
        $resource = new Resource();
        foreach (['save', 'reload', 'destroy'] as $method) {
            try {
                $resource->{$method}();
                self::fail('Expected lifecycle method to reject an unattached resource.');
            } catch (\LogicException $exception) {
                self::assertStringContainsString('service', $exception->getMessage());
            }
        }

        $attached = new Resource([], new Service('Unknown', $this->mockHttpClient([])));
        try {
            $attached->reload();
            self::fail('Expected reload to require an ID.');
        } catch (\LogicException $exception) {
            self::assertStringContainsString('ID', $exception->getMessage());
        }
    }

    public function testCollectionSupportsIterationMetadataAndControlledMutation(): void
    {
        $first = new Resource(['id' => 'one']);
        $second = new Resource(['id' => 'two']);
        $collection = new Collection([$first], ['page' => 1], ['next' => '/page/2']);

        self::assertCount(1, $collection);
        self::assertSame([$first], $collection->all());
        self::assertSame(['page' => 1], $collection->meta());
        self::assertSame(['next' => '/page/2'], $collection->links());
        self::assertSame([$first], iterator_to_array($collection));
        self::assertTrue($collection->offsetExists(0));
        self::assertFalse($collection->offsetExists('0'));
        self::assertSame($first, $collection->offsetGet(0));
        self::assertNull($collection->offsetGet('0'));
        $collection->offsetSet(null, $second);
        $collection->offsetSet(0, $second);
        self::assertSame([$second, $second], $collection->all());
        $collection->offsetUnset(0);
        $collection->offsetUnset('0');
        self::assertSame([$second], $collection->all());
        self::assertSame([
            'data' => [['id' => 'two']],
            'meta' => ['page' => 1],
            'links' => ['next' => '/page/2'],
        ], $collection->jsonSerialize());

        foreach ([[0, 'invalid'], ['bad', $first]] as $arguments) {
            try {
                $collection->offsetSet($arguments[0], $arguments[1]);
                self::fail('Expected invalid collection mutation.');
            } catch (\InvalidArgumentException $exception) {
                self::assertStringContainsString('Fleetbase collection', $exception->getMessage());
            }
        }
    }

    public function testServiceHydratesEnvelopesPaginationFallbacksAndActions(): void
    {
        $client = $this->mockHttpClient([
            $this->jsonResponse(['data' => [['id' => 'one']], 'meta' => ['page' => 1], 'links' => ['next' => null]]),
            $this->jsonResponse(['results' => [['id' => 'two']]]),
            $this->jsonResponse(['items' => [['id' => 'three']]]),
            $this->jsonResponse(['unrecognized' => true]),
            $this->jsonResponse(['id' => 'fallback']),
            new Response(200, ['Content-Type' => 'application/json'], '"scalar"'),
            $this->jsonResponse(['ok' => true]),
        ]);
        $places = new Service('Place', $client);
        $page = $places->paginate();
        self::assertCount(1, $page);
        self::assertSame(['page' => 1], $page->meta());
        self::assertSame(['next' => null], $page->links());
        $all = $places->findAll();
        $queried = $places->query();
        self::assertIsArray($all);
        self::assertIsArray($queried);
        self::assertContainsOnlyInstancesOf(Place::class, $all);
        self::assertContainsOnlyInstancesOf(Place::class, $queried);
        self::assertCount(0, $places->paginate());

        $fallback = (new Service('MissingResource', $client))->findRecord('fallback');
        self::assertSame(Resource::class, get_class($fallback));
        try {
            $places->findRecord('scalar');
            self::fail('Expected scalar resource payload to fail.');
        } catch (\UnexpectedValueException $exception) {
            self::assertStringContainsString('objects or arrays', $exception->getMessage());
        }
        $action = $places->action('POST', 'custom', ['value' => true]);
        self::assertIsObject($action);
        self::assertSame(true, get_object_vars($action)['ok'] ?? null);
        self::assertSame('places/id%20with%20space/child', $places->uriForResource('id with space', 'child'));
        self::assertSame('places', $places->uri(''));
        self::assertSame([], $places->getOptions());
        self::assertSame($client, $places->getClient());
    }

    /** @param mixed $data */
    private function jsonResponse($data, int $status = 200): Response
    {
        return new Response($status, ['Content-Type' => 'application/json'], json_encode($data, JSON_THROW_ON_ERROR));
    }
}
