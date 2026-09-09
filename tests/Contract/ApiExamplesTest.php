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
        // Read the expected count from the contract lock rather than pinning a
        // literal here. The literal is one more place to remember when the
        // contract grows, and forgetting it fails this test for a reason that
        // has nothing to do with the snippets.
        $lockContents = file_get_contents(dirname(__DIR__, 2) . '/contracts/contract-lock.json');
        self::assertIsString($lockContents);
        $lock = json_decode($lockContents, true, 512, JSON_THROW_ON_ERROR);
        self::assertIsArray($lock);
        $lockSources = $lock['sources'] ?? null;
        self::assertIsArray($lockSources);
        $lockPostman = $lockSources['postman'] ?? null;
        self::assertIsArray($lockPostman);
        $expectedRequests = $lockPostman['expected_requests'] ?? null;
        self::assertIsInt($expectedRequests);

        $contents = file_get_contents(dirname(__DIR__, 2) . '/docs/api-examples.md');
        self::assertIsString($contents);
        preg_match_all('/```php\n(.*?)\n```/s', $contents, $matches);
        $snippets = $matches[1];
        self::assertCount($expectedRequests, $snippets);

        $catalogContents = file_get_contents(dirname(__DIR__, 2) . '/contracts/php-sdk-examples.json');
        self::assertIsString($catalogContents);
        $catalog = json_decode($catalogContents, true, 512, JSON_THROW_ON_ERROR);
        self::assertIsArray($catalog);
        $catalogExamples = $catalog['examples'] ?? null;
        self::assertIsArray($catalogExamples);
        self::assertCount($expectedRequests, $catalogExamples);
        self::assertSame($snippets, array_column(array_values($catalogExamples), 'call'));
        $dispatchExample = $catalogExamples['fleetbase-api-orders-dispatch-an-order'] ?? null;
        self::assertIsArray($dispatchExample);
        self::assertSame(
            '$result = $fleetbase->orders->dispatchOrder($orderId);',
            $dispatchExample['call'] ?? null
        );
        $passwordExample = $catalogExamples['fleetbase-api-drivers-change-driver-password'] ?? null;
        $vehicleTrailers = $catalogExamples['fleetbase-api-trailers-list-vehicle-trailers'] ?? null;
        self::assertIsArray($vehicleTrailers);
        self::assertSame('$result = $fleetbase->vehicles->listVehicleTrailers($vehicleId);', $vehicleTrailers['call'] ?? null);
        $deviceAttachment = $catalogExamples['fleetbase-api-trailers-attach-device-to-trailer'] ?? null;
        self::assertIsArray($deviceAttachment);
        self::assertIsString($deviceAttachment['call'] ?? null);
        self::assertStringContainsString('$fleetbase->devices->attachDevice(', $deviceAttachment['call']);
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
