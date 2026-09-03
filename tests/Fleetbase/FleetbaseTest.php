<?php

declare(strict_types=1);

namespace Fleetbase\Sdk\Test\Fleetbase;

use Fleetbase\Sdk\Fleetbase;
use Fleetbase\Sdk\Service;
use Fleetbase\Sdk\Test\TestCase;

final class FleetbaseTest extends TestCase
{
    public function testInitializesLegacyFacadeAndStores(): void
    {
        $sdk = new Fleetbase('test_public_key');

        foreach (self::services() as $store => $serviceClass) {
            self::assertInstanceOf($serviceClass, $sdk->{$store});
            self::assertSame($sdk->{$store}, $sdk->service($store));
            self::assertSame($sdk->{$store}, $sdk->{$store}());
        }
    }

    public function testPreservesConfigurationAndVersion(): void
    {
        $sdk = new Fleetbase('test_public_key', [
            'version' => 'v2',
            'host' => 'https://fleetbase.example.test/base',
            'namespace' => 'custom',
        ], true);

        $this->assertSame('v2', $sdk->getVersion());
        $this->assertSame([
            'version' => 'v2',
            'host' => 'https://fleetbase.example.test/base',
            'namespace' => 'custom',
            'debug' => true,
            'publicKey' => 'test_public_key',
        ], $sdk->getOptions());
    }

    public function testNewInstanceKeepsLegacyVariadicFactory(): void
    {
        $sdk = Fleetbase::newInstance('test_public_key', ['namespace' => 'api']);

        $this->assertSame('api', $sdk->getOptions()['namespace']);
    }

    public function testRekeysFacadeAndRejectsInvalidFactoriesAndServices(): void
    {
        $sdk = new Fleetbase('old_key', ['host' => 'https://self.example.test/root', 'namespace' => 'api/v2'], true);
        $replacement = $sdk->setApiKey('new_key');
        self::assertNotSame($sdk, $replacement);
        self::assertSame('new_key', $replacement->getOptions()['publicKey']);
        self::assertSame('https://self.example.test/root', $replacement->getOptions()['host']);
        self::assertSame('api/v2', $replacement->getOptions()['namespace']);
        self::assertTrue($replacement->getOptions()['debug']);

        foreach ([[], [123], ['key', 'invalid'], ['key', [], 'invalid']] as $arguments) {
            try {
                Fleetbase::newInstance(...$arguments);
                self::fail('Expected invalid factory arguments.');
            } catch (\InvalidArgumentException $exception) {
                self::assertStringContainsString('expects an API key', $exception->getMessage());
            }
        }

        foreach (['missing', 'client'] as $name) {
            try {
                $sdk->service($name);
                self::fail('Expected an unknown service.');
            } catch (\InvalidArgumentException $exception) {
                self::assertStringContainsString($name, $exception->getMessage());
            }
        }
    }

    /** @return array<string, class-string<Service>> */
    private static function services(): array
    {
        return [
            'orders' => \Fleetbase\Sdk\Services\OrderService::class,
            'entities' => \Fleetbase\Sdk\Services\EntityService::class,
            'places' => \Fleetbase\Sdk\Services\PlaceService::class,
            'drivers' => \Fleetbase\Sdk\Services\DriverService::class,
            'vehicles' => \Fleetbase\Sdk\Services\VehicleService::class,
            'vendors' => \Fleetbase\Sdk\Services\VendorService::class,
            'contacts' => \Fleetbase\Sdk\Services\ContactService::class,
            'serviceAreas' => \Fleetbase\Sdk\Services\ServiceAreaService::class,
            'zones' => \Fleetbase\Sdk\Services\ZoneService::class,
            'trackingStatuses' => \Fleetbase\Sdk\Services\TrackingStatusService::class,
            'serviceRates' => \Fleetbase\Sdk\Services\ServiceRateService::class,
            'serviceQuotes' => \Fleetbase\Sdk\Services\ServiceQuoteService::class,
            'customers' => \Fleetbase\Sdk\Services\CustomerService::class,
            'devices' => \Fleetbase\Sdk\Services\DeviceService::class,
            'equipment' => \Fleetbase\Sdk\Services\EquipmentService::class,
            'fleets' => \Fleetbase\Sdk\Services\FleetService::class,
            'fuelReports' => \Fleetbase\Sdk\Services\FuelReportService::class,
            'fuelTransactions' => \Fleetbase\Sdk\Services\FuelTransactionService::class,
            'geofences' => \Fleetbase\Sdk\Services\GeofenceService::class,
            'issues' => \Fleetbase\Sdk\Services\IssueService::class,
            'labels' => \Fleetbase\Sdk\Services\LabelService::class,
            'manifests' => \Fleetbase\Sdk\Services\ManifestService::class,
            'onboard' => \Fleetbase\Sdk\Services\OnboardService::class,
            'orchestrator' => \Fleetbase\Sdk\Services\OrchestratorService::class,
            'orderConfigs' => \Fleetbase\Sdk\Services\OrderConfigService::class,
            'organizations' => \Fleetbase\Sdk\Services\OrganizationService::class,
            'parts' => \Fleetbase\Sdk\Services\PartService::class,
            'payloads' => \Fleetbase\Sdk\Services\PayloadService::class,
            'purchaseRates' => \Fleetbase\Sdk\Services\PurchaseRateService::class,
            'sensors' => \Fleetbase\Sdk\Services\SensorService::class,
            'trackingNumbers' => \Fleetbase\Sdk\Services\TrackingNumberService::class,
            'workOrders' => \Fleetbase\Sdk\Services\WorkOrderService::class,
            'chatChannels' => \Fleetbase\Sdk\Services\ChatChannelService::class,
            'comments' => \Fleetbase\Sdk\Services\CommentService::class,
            'files' => \Fleetbase\Sdk\Services\FileService::class,
        ];
    }
}
