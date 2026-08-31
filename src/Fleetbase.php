<?php

/**
 * This file is part of the fleetbase/fleetbase-php library.
 *
 * @copyright Copyright (c) Fleetbase Pte Ltd. <ron@fleetbase.io>
 * @license https://www.gnu.org/licenses/agpl-3.0.html AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace Fleetbase\Sdk;

use Fleetbase\Sdk\Services\OrderService;

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
        $this->entities = new Service('Entity', $this->client);
        $this->places = new Service('Place', $this->client);
        $this->drivers = new Service('Driver', $this->client);
        $this->vehicles = new Service('Vehicle', $this->client);
        $this->vendors = new Service('Vendor', $this->client);
        $this->contacts = new Service('Contact', $this->client);
        $this->serviceAreas = new Service('ServiceArea', $this->client);
        $this->zones = new Service('Zone', $this->client);
        $this->trackingStatuses = new Service('TrackingStatus', $this->client);
        $this->serviceRates = new Service('ServiceRate', $this->client);
        $this->serviceQuotes = new Service('ServiceQuote', $this->client);
        $this->customers = new Service('Customer', $this->client);
        $this->devices = new Service('Device', $this->client);
        $this->equipment = new Service('Equipment', $this->client);
        $this->fleets = new Service('Fleet', $this->client);
        $this->fuelReports = new Service('FuelReport', $this->client);
        $this->fuelTransactions = new Service('FuelTransaction', $this->client);
        $this->geofences = new Service('Geofence', $this->client);
        $this->issues = new Service('Issue', $this->client);
        $this->labels = new Service('Label', $this->client);
        $this->manifests = new Service('Manifest', $this->client);
        $this->onboard = new Service('Onboard', $this->client, ['namespace' => 'onboard']);
        $this->orchestrator = new Service('Orchestrator', $this->client, ['namespace' => 'orchestrator']);
        $this->orderConfigs = new Service('OrderConfig', $this->client);
        $this->organizations = new Service('Organization', $this->client);
        $this->parts = new Service('Part', $this->client);
        $this->payloads = new Service('Payload', $this->client);
        $this->purchaseRates = new Service('PurchaseRate', $this->client);
        $this->sensors = new Service('Sensor', $this->client);
        $this->trackingNumbers = new Service('TrackingNumber', $this->client);
        $this->workOrders = new Service('WorkOrder', $this->client);
        $this->chatChannels = new Service('ChatChannel', $this->client);
        $this->comments = new Service('Comment', $this->client);
        $this->files = new Service('File', $this->client);
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
