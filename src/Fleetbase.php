<?php

/**
 * This file is part of the fleetbase/fleetbase-php library.
 *
 * @copyright Copyright (c) Fleetbase Pte Ltd. <ron@fleetbase.io>
 * @license https://www.gnu.org/licenses/agpl-3.0.html AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace Fleetbase\Sdk;

use Fleetbase\Sdk\Services\ChatChannelService;
use Fleetbase\Sdk\Services\CommentService;
use Fleetbase\Sdk\Services\ContactService;
use Fleetbase\Sdk\Services\CustomerService;
use Fleetbase\Sdk\Services\DeviceService;
use Fleetbase\Sdk\Services\DriverService;
use Fleetbase\Sdk\Services\EntityService;
use Fleetbase\Sdk\Services\EquipmentService;
use Fleetbase\Sdk\Services\FileService;
use Fleetbase\Sdk\Services\FleetService;
use Fleetbase\Sdk\Services\FuelReportService;
use Fleetbase\Sdk\Services\FuelTransactionService;
use Fleetbase\Sdk\Services\GeofenceService;
use Fleetbase\Sdk\Services\IssueService;
use Fleetbase\Sdk\Services\LabelService;
use Fleetbase\Sdk\Services\ManifestService;
use Fleetbase\Sdk\Services\OnboardService;
use Fleetbase\Sdk\Services\OrchestratorService;
use Fleetbase\Sdk\Services\OrderConfigService;
use Fleetbase\Sdk\Services\OrderService;
use Fleetbase\Sdk\Services\OrganizationService;
use Fleetbase\Sdk\Services\PartService;
use Fleetbase\Sdk\Services\PayloadService;
use Fleetbase\Sdk\Services\PlaceService;
use Fleetbase\Sdk\Services\PurchaseRateService;
use Fleetbase\Sdk\Services\SensorService;
use Fleetbase\Sdk\Services\ServiceAreaService;
use Fleetbase\Sdk\Services\ServiceQuoteService;
use Fleetbase\Sdk\Services\ServiceRateService;
use Fleetbase\Sdk\Services\TrackingNumberService;
use Fleetbase\Sdk\Services\TrackingStatusService;
use Fleetbase\Sdk\Services\VehicleService;
use Fleetbase\Sdk\Services\VendorService;
use Fleetbase\Sdk\Services\WorkOrderService;
use Fleetbase\Sdk\Services\ZoneService;

/** @phpstan-consistent-constructor */
class Fleetbase
{
    private string $version;

    /** @var array<string, mixed> */
    private array $options;

    /** @var HttpClient */
    public $client;

    /** @var OrderService */
    public $orders;

    /** @var Service */
    public $entities;

    /** @var Service */
    public $places;

    /** @var Service */
    public $drivers;

    /** @var Service */
    public $vehicles;

    /** @var Service */
    public $vendors;

    /** @var Service */
    public $contacts;

    /** @var Service */
    public $serviceAreas;

    /** @var Service */
    public $zones;

    /** @var Service */
    public $trackingStatuses;

    /** @var Service */
    public $serviceRates;

    /** @var Service */
    public $serviceQuotes;

    /** @var Service */
    public $customers;

    /** @var Service */
    public $devices;

    /** @var Service */
    public $equipment;

    /** @var Service */
    public $fleets;

    /** @var Service */
    public $fuelReports;

    /** @var Service */
    public $fuelTransactions;

    /** @var Service */
    public $geofences;

    /** @var Service */
    public $issues;

    /** @var Service */
    public $labels;

    /** @var Service */
    public $manifests;

    /** @var Service */
    public $onboard;

    /** @var Service */
    public $orchestrator;

    /** @var Service */
    public $orderConfigs;

    /** @var Service */
    public $organizations;

    /** @var Service */
    public $parts;

    /** @var Service */
    public $payloads;

    /** @var Service */
    public $purchaseRates;

    /** @var Service */
    public $sensors;

    /** @var Service */
    public $trackingNumbers;

    /** @var Service */
    public $workOrders;

    /** @var Service */
    public $chatChannels;

    /** @var Service */
    public $comments;

    /** @var Service */
    public $files;

    /** @param array<string, mixed> $config */
    public function __construct(string $publicKey, array $config = [], bool $debug = false)
    {
        $configuration = new Configuration($publicKey, $config, $debug);
        $this->version = $configuration->getVersion();
        $this->options = $configuration->toArray();
        $this->client = new HttpClient($this->options);

        $this->orders = new OrderService($this->client);
        $this->entities = new EntityService($this->client);
        $this->places = new PlaceService($this->client);
        $this->drivers = new DriverService($this->client);
        $this->vehicles = new VehicleService($this->client);
        $this->vendors = new VendorService($this->client);
        $this->contacts = new ContactService($this->client);
        $this->serviceAreas = new ServiceAreaService($this->client);
        $this->zones = new ZoneService($this->client);
        $this->trackingStatuses = new TrackingStatusService($this->client);
        $this->serviceRates = new ServiceRateService($this->client);
        $this->serviceQuotes = new ServiceQuoteService($this->client);
        $this->customers = new CustomerService($this->client);
        $this->devices = new DeviceService($this->client);
        $this->equipment = new EquipmentService($this->client);
        $this->fleets = new FleetService($this->client);
        $this->fuelReports = new FuelReportService($this->client);
        $this->fuelTransactions = new FuelTransactionService($this->client);
        $this->geofences = new GeofenceService($this->client);
        $this->issues = new IssueService($this->client);
        $this->labels = new LabelService($this->client);
        $this->manifests = new ManifestService($this->client);
        $this->onboard = new OnboardService($this->client);
        $this->orchestrator = new OrchestratorService($this->client);
        $this->orderConfigs = new OrderConfigService($this->client);
        $this->organizations = new OrganizationService($this->client);
        $this->parts = new PartService($this->client);
        $this->payloads = new PayloadService($this->client);
        $this->purchaseRates = new PurchaseRateService($this->client);
        $this->sensors = new SensorService($this->client);
        $this->trackingNumbers = new TrackingNumberService($this->client);
        $this->workOrders = new WorkOrderService($this->client);
        $this->chatChannels = new ChatChannelService($this->client);
        $this->comments = new CommentService($this->client);
        $this->files = new FileService($this->client);
    }

    public function setApiKey(string $publicKey): Fleetbase
    {
        $config = $this->options;
        unset($config['publicKey'], $config['debug']);
        return static::newInstance($publicKey, $config, (bool) $this->options['debug']);
    }

    public static function newInstance(): Fleetbase
    {
        /** @var array<int, mixed> $args */
        $args = func_get_args();
        $publicKey = $args[0] ?? null;
        $config = $args[1] ?? [];
        $debug = $args[2] ?? false;
        if (!is_string($publicKey) || !is_array($config) || !is_bool($debug)) {
            throw new \InvalidArgumentException('Fleetbase::newInstance() expects an API key, configuration array, and debug boolean.');
        }

        $normalizedConfig = [];
        foreach ($config as $key => $value) {
            if (is_string($key)) {
                $normalizedConfig[$key] = $value;
            }
        }

        return new static($publicKey, $normalizedConfig, $debug);
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    /** @return array<string, mixed> */
    public function getOptions(): array
    {
        return $this->options;
    }

    public function orders(): OrderService
    {
        return $this->orders;
    }

    public function service(string $name): Service
    {
        if (!property_exists($this, $name) || !$this->{$name} instanceof Service) {
            throw new \InvalidArgumentException(sprintf('Unknown Fleetbase service "%s".', $name));
        }

        return $this->{$name};
    }

    public function places(): Service
    {
        return $this->places;
    }

    public function drivers(): Service
    {
        return $this->drivers;
    }

    public function vehicles(): Service
    {
        return $this->vehicles;
    }

    public function vendors(): Service
    {
        return $this->vendors;
    }

    public function contacts(): Service
    {
        return $this->contacts;
    }

    public function payloads(): Service
    {
        return $this->payloads;
    }

    public function organizations(): Service
    {
        return $this->organizations;
    }

    public function comments(): Service
    {
        return $this->comments;
    }

    public function files(): Service
    {
        return $this->files;
    }

    public function chatChannels(): Service
    {
        return $this->chatChannels;
    }
}
