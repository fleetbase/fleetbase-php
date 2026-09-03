<?php

declare(strict_types=1);

namespace Fleetbase\Sdk\Test\Contract;

use Fleetbase\Sdk\Fleetbase;
use Fleetbase\Sdk\Test\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;

final class ApiExamplesTest extends TestCase
{
    public function testEveryGeneratedDocumentationSnippetExecutes(): void
    {
        $contents = file_get_contents(dirname(__DIR__, 2) . '/docs/api-examples.md');
        self::assertIsString($contents);
        preg_match_all('/```php\n(.*?)\n```/s', $contents, $matches);
        $snippets = $matches[1];
        self::assertCount(220, $snippets);

        $history = [];
        $responses = array_fill(0, count($snippets), new Response(200, ['Content-Type' => 'application/json'], '{}'));
        $handler = HandlerStack::create(new MockHandler($responses));
        $handler->push(Middleware::history($history));
        if (!is_array($history)) {
            throw new \LogicException('Guzzle history middleware did not preserve its array container.');
        }
        $fleetbase = new Fleetbase('test_public_key', [
            'host' => 'https://api.example.test',
            'namespace' => 'v1',
            'httpClient' => new Client(['handler' => $handler]),
        ]);

        foreach ($snippets as $index => $snippet) {
            self::assertIsString($snippet);
            eval($snippet);
            self::assertCount($index + 1, $history);
        }
    }
}
