<?php

declare(strict_types=1);

namespace Fleetbase\Sdk\Test;

use Fleetbase\Sdk\Resource;
use Fleetbase\Sdk\Resources\Place;
use Fleetbase\Sdk\Service;
use GuzzleHttp\Psr7\Response;
use JsonSerializable;
use Psr\Http\Message\RequestInterface;

final class ResourceServiceTest extends TestCase
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
        self::assertSame(['nested' => ['id' => 'nested']], $resource->getAttributes(['nested']));
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

    public function testServiceHydratesListShapesFallbacksAndActions(): void
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
        $all = $places->findAll();
        $queried = $places->query();
        $items = $places->query();
        $unrecognized = $places->findAll();
        self::assertIsArray($all);
        self::assertIsArray($queried);
        self::assertIsArray($items);
        self::assertContainsOnlyInstancesOf(Place::class, $all);
        self::assertContainsOnlyInstancesOf(Place::class, $queried);
        self::assertContainsOnlyInstancesOf(Place::class, $items);
        self::assertSame('one', $all[0]->getAttribute('id'));
        self::assertSame('two', $queried[0]->getAttribute('id'));
        self::assertSame('three', $items[0]->getAttribute('id'));
        self::assertIsObject($unrecognized);
        self::assertTrue(get_object_vars($unrecognized)['unrecognized'] ?? false);

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

    public function testResourceCoversSaveDefaultsValidationAndFailureState(): void
    {
        $client = $this->mockHttpClient([
            $this->jsonResponse(['id' => 'new_1', 'name' => 'New']),
            $this->jsonResponse(['id' => 'same_1', 'name' => 'Same']),
            $this->jsonResponse(['id' => 'same_1', 'name' => 'Same', 'deleted' => false]),
            new \RuntimeException('save failed'),
        ]);
        $service = new Service('Place', $client);

        $new = new Place(['name' => 'New'], $service);
        self::assertInstanceOf(Place::class, $new->save(null));
        self::assertSame('new_1', $new->getAttribute('id'));

        $same = new Place(['id' => 'same_1', 'name' => 'Same'], $service);
        self::assertInstanceOf(Place::class, $same->save());
        self::assertSame('{"id":"same_1","name":"Same"}', (string) $this->requestAt(1)->getBody());
        self::assertInstanceOf(Place::class, $same->destroy());
        self::assertFalse($same->__get('isDestroyed'));

        try {
            $same->update(['name' => 'Failure']);
            self::fail('Expected update failure.');
        } catch (\RuntimeException $exception) {
            self::assertSame('save failed', $exception->getMessage());
            self::assertFalse($same->__get('isSaving'));
        }

        foreach (['create', 'update', 'destroy'] as $method) {
            try {
                $arguments = $method === 'update' ? [[], 'invalid'] : ($method === 'create' ? [[], 'invalid'] : ['invalid']);
                (new \ReflectionMethod($same, $method))->invokeArgs($same, $arguments);
                self::fail('Expected invalid resource options.');
            } catch (\InvalidArgumentException $exception) {
                self::assertStringContainsString('options', $exception->getMessage());
            }
        }

        $same->setAttribute('name', 'Changed')->setAttribute('name', 'Same');
        self::assertFalse($same->isDirty('name'));
        self::assertFalse((new \ReflectionMethod($same, 'isDirty'))->invoke($same, 42));
        self::assertFalse((new \ReflectionMethod($same, 'isDirty'))->invoke($same, ['missing', 42]));
        self::assertFalse((new \ReflectionMethod($same, 'isAttributeFilled'))->invoke($same, 42));

        $boundaryClient = $this->mockHttpClient([
            $this->jsonResponse(['id' => 'boundary']),
            $this->jsonResponse(['id' => 'boundary']),
        ]);
        $boundary = new Place(['id' => 'boundary'], new Service('Place', $boundaryClient));
        self::assertInstanceOf(Place::class, (new \ReflectionMethod($boundary, 'create'))->invoke($boundary, 'invalid'));
        self::assertInstanceOf(Place::class, (new \ReflectionMethod($boundary, 'update'))->invoke($boundary, 'invalid'));
    }

    public function testServiceCoversDirectCollectionsEnvelopesDeletionAndEndpointBoundaries(): void
    {
        $client = $this->mockHttpClient([
            $this->jsonResponse([['id' => 'direct']]),
            $this->jsonResponse(['data' => ['id' => 'enveloped']]),
            $this->jsonResponse(['id' => 'deleted']),
            $this->jsonResponse(['ok' => true]),
            $this->jsonResponse(['ok' => true]),
        ]);
        $service = new class ('Place', $client, ['namespace' => '/custom/']) extends Service {
            /**
             * @param array<string, mixed> $parameters
             * @param array<string, mixed> $options
             * @return mixed
             */
            public function callEndpoint(string $method, string $template, array $parameters = [], array $options = [])
            {
                return $this->endpoint($method, $template, $parameters, $options);
            }

        };

        $all = $service->findAll();
        self::assertIsArray($all);
        self::assertCount(1, $all);
        self::assertSame('enveloped', $service->findRecord('enveloped')->getAttribute('id'));
        self::assertSame('deleted', $service->destroy(new Place(['id' => 'deleted']))->getAttribute('id'));
        foreach ([null, '', 123, new Place()] as $invalid) {
            try {
                (new \ReflectionMethod($service, 'destroy'))->invoke($service, $invalid);
                self::fail('Expected deletion to require an ID.');
            } catch (\InvalidArgumentException $exception) {
                self::assertStringContainsString('ID', $exception->getMessage());
            }
        }

        $result = $service->callEndpoint('GET', '{{base_url}}/{{namespace}}/places/{{place}}?existing=yes', [
            'place' => 'place one',
            'ignored' => 'value',
        ], ['query' => ['page' => 2]]);
        self::assertIsObject($result);
        self::assertTrue(get_object_vars($result)['ok'] ?? false);
        $service->callEndpoint('POST', 'places/:place', [
            'place' => 'place-two',
            'body' => ['name' => 'Body', 0 => 'ignored'],
        ], ['query' => []]);
        self::assertSame('existing=yes&page=2&ignored=value', $this->requestAt(3)->getUri()->getQuery());
        self::assertSame('{"name":"Body"}', (string) $this->requestAt(4)->getBody());

        try {
            $service->callEndpoint('GET', 'places/{{missing}}');
            self::fail('Expected a required endpoint parameter.');
        } catch (\InvalidArgumentException $exception) {
            self::assertStringContainsString('missing', $exception->getMessage());
        }
    }

    public function testGeneratedEndpointArgumentNormalizationRejectsAmbiguityAndInvalidValues(): void
    {
        $client = $this->mockHttpClient([
            $this->jsonResponse(['ok' => true]),
            $this->jsonResponse(['ok' => true]),
        ]);
        $service = new class ('Place', $client) extends Service {
            /**
             * @param array<int, string> $pathParameters
             * @param array<int, mixed> $arguments
             * @return mixed
             */
            public function callArguments(
                string $method,
                string $template,
                array $pathParameters,
                string $requestData,
                array $arguments
            ) {
                return $this->endpointFromArguments($method, $template, $pathParameters, $requestData, $arguments);
            }
        };

        $service->callArguments('GET', 'places/:id', ['id'], 'query', [new Resource(['id' => 'resource/id']), []]);
        $resourceTransaction = $this->history[0] ?? null;
        self::assertIsArray($resourceTransaction);
        $resourceRequest = $resourceTransaction['request'] ?? null;
        self::assertInstanceOf(RequestInterface::class, $resourceRequest);
        self::assertSame('/v1/places/resource%2Fid', $resourceRequest->getUri()->getPath());
        $service->callArguments('POST', 'places/:id/files', ['id'], 'multipart', [
            'place_1',
            [['name' => 'file', 'contents' => 'contents']],
        ]);
        $multipartTransaction = $this->history[1] ?? null;
        self::assertIsArray($multipartTransaction);
        $multipartRequest = $multipartTransaction['request'] ?? null;
        self::assertInstanceOf(RequestInterface::class, $multipartRequest);
        self::assertStringContainsString('name="file"', (string) $multipartRequest->getBody());

        $invalidCases = [
            ['body', 'places', [], 'unsupported', []],
            ['accept only', 'places', [], 'body', [[], [], []]],
            ['request data', 'places', [], 'body', ['invalid']],
            ['request options', 'places', [], 'body', [[], 'invalid']],
            ['both data and request options', 'places', [], 'query', [['page' => 1], ['query' => ['page' => 2]]]],
            ['conflicts', 'places', [], 'body', [['name' => 'x'], ['body' => '{}']]],
            ['conflicts', 'places', [], 'body', [['name' => 'x'], ['multipart' => []]]],
            ['conflicts', 'places', [], 'body', [['name' => 'x'], ['form_params' => []]]],
            ['parameters and request options', 'places/:id', ['id'], 'body', [['id' => 'place_1'], [], []]],
            ['request options', 'places/:id', ['id'], 'body', [['id' => 'place_1'], 'invalid']],
            ['child', 'places/:id/children/:child', ['id', 'child'], 'body', ['place_1']],
            ['non-empty scalar', 'places/:id', ['id'], 'body', [new \stdClass()]],
            ['non-empty scalar', 'places/:id', ['id'], 'body', ['']],
            ['Too many', 'places/:id', ['id'], 'body', ['place_1', [], [], []]],
            ['request data', 'places/:id', ['id'], 'body', ['place_1', 'invalid']],
            ['request options', 'places/:id', ['id'], 'body', ['place_1', [], 'invalid']],
            ['both data and request options', 'places/:id', ['id'], 'query', ['place_1', ['page' => 1], ['query' => ['page' => 2]]]],
            ['both data and request options', 'places/:id', ['id'], 'multipart', ['place_1', [['name' => 'file', 'contents' => 'x']], ['multipart' => []]]],
            ['conflicts', 'places/:id', ['id'], 'body', ['place_1', ['name' => 'x'], ['body' => '{}']]],
            ['conflicts', 'places/:id', ['id'], 'body', ['place_1', ['name' => 'x'], ['multipart' => []]]],
            ['conflicts', 'places/:id', ['id'], 'body', ['place_1', ['name' => 'x'], ['form_params' => []]]],
            ['list of parts', 'files', [], 'multipart', [['file' => 'x']]],
            ['string name and contents', 'files', [], 'multipart', [[['name' => 1, 'contents' => 'x']]]],
        ];

        foreach ($invalidCases as [$message, $template, $pathParameters, $requestData, $arguments]) {
            try {
                $service->callArguments('POST', $template, $pathParameters, $requestData, $arguments);
                self::fail('Expected invalid generated endpoint arguments.');
            } catch (\InvalidArgumentException $exception) {
                self::assertStringContainsString($message, $exception->getMessage());
            }
        }
    }

    public function testReloadRejectsUnexpectedServiceResultAndRestoresState(): void
    {
        $service = new class ('Place', $this->mockHttpClient([])) extends Service {
            /** @return mixed */
            public function findRecord(string $id, array $options = [])
            {
                return (object) ['not' => 'a resource'];
            }
        };
        $resource = new Place(['id' => 'place_1'], $service);
        try {
            $resource->reload();
            self::fail('Expected invalid reload result.');
        } catch (\UnexpectedValueException $exception) {
            self::assertStringContainsString('did not return a resource', $exception->getMessage());
            self::assertFalse($resource->__get('isReloading'));
        }
    }

    public function testFindAllAndQueryPreserveUnrecognizedRawResponses(): void
    {
        $client = $this->mockHttpClient([
            $this->jsonResponse(['unrecognized' => 'all']),
            $this->jsonResponse(['unrecognized' => 'query']),
        ]);
        $service = new Service('Place', $client);

        $all = $service->findAll();
        $query = $service->query(['filter' => 'active']);
        self::assertIsObject($all);
        self::assertIsObject($query);
        self::assertSame('all', get_object_vars($all)['unrecognized'] ?? null);
        self::assertSame('query', get_object_vars($query)['unrecognized'] ?? null);
    }

    public function testPreservesLegacyResourceConstructorAliases(): void
    {
        $classes = [
            \Fleetbase\Sdk\Resources\Contact::class,
            \Fleetbase\Sdk\Resources\Driver::class,
            \Fleetbase\Sdk\Resources\Entity::class,
            \Fleetbase\Sdk\Resources\Payload::class,
            \Fleetbase\Sdk\Resources\Place::class,
            \Fleetbase\Sdk\Resources\ServiceArea::class,
            \Fleetbase\Sdk\Resources\ServiceQuote::class,
            \Fleetbase\Sdk\Resources\ServiceRate::class,
            \Fleetbase\Sdk\Resources\TrackingStatus::class,
            \Fleetbase\Sdk\Resources\Vehicle::class,
            \Fleetbase\Sdk\Resources\Vendor::class,
            \Fleetbase\Sdk\Resources\Waypoint::class,
            \Fleetbase\Sdk\Resources\Zone::class,
        ];

        foreach ($classes as $class) {
            $resource = new $class();
            $resource->__constructor(['id' => 'legacy']);
            self::assertSame('legacy', $resource->getAttribute('id'));
        }
    }

    /** @param mixed $data */
    private function jsonResponse($data, int $status = 200): Response
    {
        return new Response($status, ['Content-Type' => 'application/json'], json_encode($data, JSON_THROW_ON_ERROR));
    }

    private function requestAt(int $index): \Psr\Http\Message\RequestInterface
    {
        $transaction = $this->history[$index] ?? null;
        self::assertIsArray($transaction);
        $request = $transaction['request'] ?? null;
        self::assertInstanceOf(\Psr\Http\Message\RequestInterface::class, $request);
        return $request;
    }
}
