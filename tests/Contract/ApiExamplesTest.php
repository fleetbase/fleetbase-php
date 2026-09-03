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

        $catalogContents = file_get_contents(dirname(__DIR__, 2) . '/contracts/php-sdk-examples.json');
        self::assertIsString($catalogContents);
        $catalog = json_decode($catalogContents, true, 512, JSON_THROW_ON_ERROR);
        self::assertIsArray($catalog);
        $catalogExamples = $catalog['examples'] ?? null;
        self::assertIsArray($catalogExamples);
        self::assertCount(220, $catalogExamples);
        self::assertSame($snippets, array_column(array_values($catalogExamples), 'call'));
        $dispatchExample = $catalogExamples['fleetbase-api-orders-dispatch-an-order'] ?? null;
        self::assertIsArray($dispatchExample);
        self::assertSame(
            '$result = $fleetbase->orders->dispatchOrder($orderId);',
            $dispatchExample['call'] ?? null
        );
        $passwordExample = $catalogExamples['fleetbase-api-drivers-change-driver-password'] ?? null;
        $scheduleExample = $catalogExamples['fleetbase-api-orders-schedule-an-order'] ?? null;
        self::assertIsArray($passwordExample);
        self::assertIsArray($scheduleExample);
        $passwordCall = $passwordExample['call'] ?? null;
        $scheduleCall = $scheduleExample['call'] ?? null;
        self::assertIsString($passwordCall);
        self::assertIsString($scheduleCall);
        self::assertStringContainsString("changeDriverPassword(\n    \$driverId,\n    [", $passwordCall);
        self::assertStringContainsString("scheduleOrder(\n    \$orderId,\n    [", $scheduleCall);
        foreach ($snippets as $snippet) {
            self::assertStringNotContainsString("'body' =>", $snippet);
            self::assertStringNotContainsString("\n    [],\n    []\n", $snippet);
        }

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

        $exampleRows = array_values($catalogExamples);
        foreach ($snippets as $index => $snippet) {
            self::assertIsString($snippet);
            $exampleRow = $exampleRows[$index] ?? null;
            self::assertIsArray($exampleRow);
            $variables = $exampleRow['variables'] ?? null;
            self::assertIsArray($variables);
            foreach ($variables as $name => $value) {
                self::assertIsString($name);
                ${$name} = $value;
            }
            eval($snippet);
            self::assertCount($index + 1, $history);
        }
    }
}
