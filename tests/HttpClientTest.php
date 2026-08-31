<?php

declare(strict_types=1);

namespace Fleetbase\Sdk\Test;

use Fleetbase\Sdk\Exception\AuthenticationException;
use Fleetbase\Sdk\Exception\AuthorizationException;
use Fleetbase\Sdk\Exception\ConflictException;
use Fleetbase\Sdk\Exception\DecodingException;
use Fleetbase\Sdk\Exception\NotFoundException;
use Fleetbase\Sdk\Exception\RateLimitException;
use Fleetbase\Sdk\Exception\ServerException;
use Fleetbase\Sdk\Exception\TransportException;
use Fleetbase\Sdk\Exception\UnexpectedResponseException;
use Fleetbase\Sdk\Exception\ValidationException;
use Fleetbase\Sdk\FleetbaseException;
use Fleetbase\Sdk\HttpClient;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class HttpClientTest extends TestCase
{
    public function testBuildsSupportedRequestBodyModesAndHeaders(): void
    {
        $client = $this->mockHttpClient(array_fill(0, 6, new Response(200, ['Content-Type' => 'application/json'], '{}')));
        $events = [];

        $client->get('things', ['filter' => 'active'], [
            'headers' => ['X-String' => 'yes', 'X-Many' => ['one', 2, 'two']],
            'onBefore' => function () use (&$events): void {
                $events[] = 'before';
            },
            'onAfter' => function () use (&$events): void {
                $events[] = 'after';
            },
        ]);
        $client->post('things', ['name' => 'JSON']);
        $client->put('things/1', [], ['form_params' => ['name' => 'Form Value']]);
        $client->patch('things/1', [], ['body' => new class () {
            public function __toString(): string
            {
                return 'raw-body';
            }
        }]);
        $client->delete('things/1', [], [
            'multipart' => [['name' => 'attachment', 'contents' => 'file-content']],
            'idempotency_key' => 'idem-1',
        ]);
        $client->request('HEAD', 'things', ['ignored' => 'query']);

        self::assertSame(['before', 'after'], $events);
        self::assertCount(6, $this->history);
        $get = $this->requestAt(0);
        self::assertSame('filter=active', $get->getUri()->getQuery());
        self::assertSame('Bearer test_public_key', $get->getHeaderLine('Authorization'));
        self::assertSame('yes', $get->getHeaderLine('X-String'));
        self::assertSame('one, two', $get->getHeaderLine('X-Many'));
        self::assertStringStartsWith('fleetbase-php/1.1.0 PHP/', $get->getHeaderLine('User-Agent'));

        $json = $this->requestAt(1);
        self::assertSame('application/json', $json->getHeaderLine('Content-Type'));
        self::assertSame('{"name":"JSON"}', (string) $json->getBody());

        $form = $this->requestAt(2);
        self::assertSame('application/x-www-form-urlencoded', $form->getHeaderLine('Content-Type'));
        self::assertSame('name=Form%20Value', (string) $form->getBody());

        self::assertSame('raw-body', (string) $this->requestAt(3)->getBody());
        $multipart = $this->requestAt(4);
        self::assertStringStartsWith('multipart/form-data; boundary=', $multipart->getHeaderLine('Content-Type'));
        self::assertStringContainsString('file-content', (string) $multipart->getBody());
        self::assertSame('idem-1', $multipart->getHeaderLine('Idempotency-Key'));
        self::assertSame('ignored=query', $this->requestAt(5)->getUri()->getQuery());
    }

    public function testTracksResponsesAndSupportsPlainTextAndEmptyBodies(): void
    {
        $client = $this->mockHttpClient([
            new Response(200, ['Content-Type' => 'text/plain'], 'download-content'),
            new Response(204),
        ]);

        self::assertSame('download-content', $client->get('files/1/download'));
        self::assertSame(200, $client->getLastResponse()->getStatusCode());
        self::assertSame($client->getLastPsrResponse(), $client->getLastResponse());
        self::assertNull($client->delete('files/1'));
    }

    public function testMapsEveryHttpErrorAndRetainsMetadata(): void
    {
        $cases = [
            400 => UnexpectedResponseException::class,
            401 => AuthenticationException::class,
            403 => AuthorizationException::class,
            404 => NotFoundException::class,
            409 => ConflictException::class,
            422 => ValidationException::class,
            429 => RateLimitException::class,
            500 => ServerException::class,
        ];

        foreach ($cases as $status => $exceptionClass) {
            $client = $this->mockHttpClient([new Response($status, [
                'Content-Type' => 'application/json',
                'X-Fleetbase-Request-Id' => 'request-' . $status,
            ], '{"message":"failed","code":"E_TEST","errors":{"field":["invalid"]}}')]);
            try {
                $client->get('things?api_key=must-not-leak');
                self::fail('Expected an HTTP exception.');
            } catch (FleetbaseException $exception) {
                self::assertInstanceOf($exceptionClass, $exception);
                self::assertSame($status, $exception->getStatusCode());
                self::assertSame('E_TEST', $exception->getErrorCode());
                self::assertSame(['field' => ['invalid']], $exception->getDetails());
                self::assertSame('request-' . $status, $exception->getRequestId());
                self::assertSame('GET', $exception->getRequestMethod());
                self::assertSame('https://api.example.test/v1/things', $exception->getRequestUrl());
                self::assertStringNotContainsString('must-not-leak', (string) $exception->getRequestUrl());
            }
        }
    }

    public function testRejectsMalformedJsonAndInvalidOptions(): void
    {
        $client = $this->mockHttpClient([
            new Response(200, ['Content-Type' => 'application/json'], '{bad'),
            new Response(500, ['Content-Type' => 'text/plain'], 'not-json'),
            new Response(200),
        ]);

        foreach (['json-success', 'error-response'] as $path) {
            try {
                $client->get($path);
                self::fail('Expected malformed JSON to fail.');
            } catch (DecodingException $exception) {
                self::assertSame('GET', $exception->getRequestMethod());
            }
        }

        try {
            $client->post('things', [], ['body' => []]);
            self::fail('Expected an invalid raw body exception.');
        } catch (\InvalidArgumentException $exception) {
            self::assertStringContainsString('Raw request bodies', $exception->getMessage());
        }

        try {
            (new \ReflectionMethod($client, 'get'))->invoke($client, 'things', [], 'invalid');
            self::fail('Expected invalid request options to fail.');
        } catch (\InvalidArgumentException $exception) {
            self::assertStringContainsString('options', $exception->getMessage());
        }
    }

    public function testRetriesOnlySafeOrIdempotentRequests(): void
    {
        $delays = [];
        $client = $this->mockHttpClient([
            new Response(429, ['Retry-After' => '2'], '{"message":"wait"}'),
            new Response(200, ['Content-Type' => 'application/json'], '{"ok":true}'),
            new Response(503, ['Content-Type' => 'application/json'], '{"message":"down"}'),
            new Response(503, ['Content-Type' => 'application/json'], '{"message":"down"}'),
            new Response(200, ['Content-Type' => 'application/json'], '{"ok":true}'),
        ]);
        $options = [
            'max_retries' => 1,
            'retry_delay_ms' => 5,
            'retry_sleep' => function (int $milliseconds, int $attempt) use (&$delays): void {
                $delays[] = [$milliseconds, $attempt];
            },
        ];

        $get = $client->get('things', [], $options);
        self::assertIsObject($get);
        self::assertSame(true, get_object_vars($get)['ok'] ?? null);

        try {
            $client->post('things', ['name' => 'unsafe'], $options);
            self::fail('Expected an unsafe POST not to retry.');
        } catch (ServerException $exception) {
            self::assertSame(503, $exception->getStatusCode());
        }

        $post = $client->post('things', ['name' => 'safe'], array_merge($options, ['idempotency_key' => 'safe-post']));
        self::assertIsObject($post);
        self::assertSame(true, get_object_vars($post)['ok'] ?? null);
        self::assertSame([[2000, 1], [5, 1]], $delays);
        self::assertCount(5, $this->history);
    }

    public function testMapsPsrTransportFailuresAndUsesInjectedClient(): void
    {
        $request = new Request('GET', 'https://api.example.test/v1/things');
        $connectionFailure = new ConnectException('connection refused', $request);
        $client = $this->mockHttpClient([$connectionFailure]);
        try {
            $client->get('things');
            self::fail('Expected a connection failure.');
        } catch (TransportException $exception) {
            self::assertSame($connectionFailure, $exception->getPrevious());
        }

        $psrClient = new class () implements ClientInterface {
            /** @var RequestInterface|null */
            public $request;

            public function sendRequest(RequestInterface $request): ResponseInterface
            {
                $this->request = $request;
                return new Response(200, ['Content-Type' => 'application/json'], '{"transport":"psr18"}');
            }
        };
        $injected = new HttpClient([
            'publicKey' => 'test_public_key',
            'httpClient' => $psrClient,
            'host' => 'https://self-hosted.example.test/prefix',
            'namespace' => 'api/v1',
        ]);
        $decoded = $injected->get('things');
        self::assertIsObject($decoded);
        self::assertSame('psr18', get_object_vars($decoded)['transport'] ?? null);
        self::assertInstanceOf(RequestInterface::class, $psrClient->request);
        self::assertSame('https://self-hosted.example.test/prefix/api/v1/things', (string) $psrClient->request->getUri());

        $genericFailure = new class ('failed') extends \RuntimeException implements ClientExceptionInterface {
        };
        $failingClient = new class ($genericFailure) implements ClientInterface {
            /** @var ClientExceptionInterface */
            private $exception;

            public function __construct(ClientExceptionInterface $exception)
            {
                $this->exception = $exception;
            }

            public function sendRequest(RequestInterface $request): ResponseInterface
            {
                throw $this->exception;
            }
        };
        try {
            (new HttpClient(['publicKey' => 'test_public_key', 'httpClient' => $failingClient]))->get('things');
            self::fail('Expected a PSR transport failure.');
        } catch (TransportException $exception) {
            self::assertSame($genericFailure, $exception->getPrevious());
        }
    }

    public function testMutatesHostAndNamespaceWithoutLosingConfiguration(): void
    {
        $client = $this->mockHttpClient([new Response(200)]);
        self::assertSame('https://api.example.test', $client->getHost());
        self::assertSame('v1', $client->getNamespace());
        self::assertSame($client, $client->setHost('https://self.example.test/root'));
        self::assertSame($client, $client->setNamespace('api/v2'));
        self::assertSame('https://self.example.test/root', $client->getHost());
        self::assertSame('api/v2', $client->getNamespace());
        self::assertSame('test_public_key', $client->getOptions()['publicKey']);
    }

    private function requestAt(int $index): RequestInterface
    {
        $transaction = $this->history[$index] ?? null;
        self::assertIsArray($transaction);
        $request = $transaction['request'] ?? null;
        self::assertInstanceOf(RequestInterface::class, $request);
        return $request;
    }

}
