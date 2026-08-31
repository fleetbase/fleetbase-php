<?php

declare(strict_types=1);

namespace Fleetbase\Sdk\Test;

use Fleetbase\Sdk\HttpClient;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

abstract class TestCase extends MockeryTestCase
{
    /** @var array<mixed> */
    protected array $history = [];

    /**
     * @param array<int, ResponseInterface|callable|Throwable> $responses
     */
    protected function mockHttpClient(array $responses): HttpClient
    {
        /** @var array<int, array{request: RequestInterface, response: ResponseInterface|null, error: Throwable|null, options: array<string, mixed>}> $history */
        $history = [];
        $handler = HandlerStack::create(new MockHandler($responses));
        $handler->push(Middleware::history($history));
        if (!is_array($history)) {
            throw new \LogicException('Guzzle history middleware did not preserve its array container.');
        }
        $guzzle = new Client([
            'base_uri' => 'https://api.example.test/v1/',
            'handler' => $handler,
        ]);

        $client = new HttpClient([
            'host' => 'https://api.example.test',
            'namespace' => 'v1',
            'publicKey' => 'test_public_key',
            'httpClient' => $guzzle,
        ]);
        $this->history = & $history;

        return $client;
    }
}
