<?php

declare(strict_types=1);

namespace Fleetbase\Sdk\Test;

use Fleetbase\Sdk\Arr;
use Fleetbase\Sdk\Configuration;
use Fleetbase\Sdk\Exception\InvalidConfigurationException;
use Fleetbase\Sdk\Utils;

final class ConfigurationUtilityTest extends TestCase
{
    public function testNormalizesConfigurationAndPreservesSelfHostedPrefixes(): void
    {
        $configuration = new Configuration(' key ', [
            'host' => 'https://fleetbase.example.test/root/',
            'namespace' => '/api/v2/',
            'version' => 'v2',
            'custom' => true,
        ], true);

        self::assertSame(' key ', $configuration->getApiKey());
        self::assertSame('https://fleetbase.example.test/root', $configuration->getHost());
        self::assertSame('api/v2', $configuration->getNamespace());
        self::assertSame('v2', $configuration->getVersion());
        self::assertTrue($configuration->isDebug());
        self::assertSame('https://fleetbase.example.test/root/api/v2/', $configuration->getBaseUri());
        self::assertTrue($configuration->toArray()['custom']);

        $withoutNamespace = new Configuration('key', ['namespace' => '']);
        self::assertSame('https://api.fleetbase.io/', $withoutNamespace->getBaseUri());
    }

    public function testRejectsEveryInvalidConfigurationBoundary(): void
    {
        $cases = [
            ['', [], 'API key'],
            ['key', ['version' => ''], 'version'],
            ['key', ['version' => 1], 'version'],
            ['key', ['host' => 'relative/path'], 'absolute'],
            ['key', ['host' => 'ftp://fleetbase.example.test'], 'HTTP'],
            ['key', ['namespace' => []], 'namespace'],
        ];

        foreach ($cases as $case) {
            try {
                new Configuration($case[0], $case[1]);
                self::fail('Expected invalid configuration to fail.');
            } catch (InvalidConfigurationException $exception) {
                self::assertStringContainsString($case[2], $exception->getMessage());
            }
        }
    }

    public function testArrayAndObjectUtilitiesCoverNestedAndDefaultValues(): void
    {
        self::assertTrue(Arr::every([2, 4], static function (int $value): bool {
            return $value % 2 === 0;
        }));
        self::assertFalse(Arr::every([2, 3], static function (int $value): bool {
            return $value % 2 === 0;
        }));
        self::assertTrue(Arr::any([1, 2], static function (int $value): bool {
            return $value === 2;
        }));
        self::assertFalse(Arr::any([], static function (): bool {
            return true;
        }));
        self::assertSame('first', Arr::first(['key' => 'first']));
        self::assertSame(-1, Arr::first([]));

        $target = ['child' => (object) ['value' => 42]];
        self::assertSame($target, Utils::get($target, null));
        self::assertSame($target, Utils::get($target, ' '));
        self::assertSame(42, Utils::get($target, 'child.value'));
        self::assertSame('fallback', Utils::get($target, 'child.missing', static function (): string {
            return 'fallback';
        }));
        self::assertSame('fallback', Utils::get((object) [], 'missing', 'fallback'));
        self::assertSame('value', Utils::value('value'));
        self::assertSame('resolved', Utils::value(static function (): string {
            return 'resolved';
        }));
        self::assertSame('companies', Utils::pluralize('company'));
        self::assertSame('FuelReport', Utils::classify('fuel_report'));
        self::assertSame('tracking-statuses', Utils::createNamespace('TrackingStatus'));
    }
}
