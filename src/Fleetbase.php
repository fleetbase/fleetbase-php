<?php

/**
 * This file is part of the fleetbase/fleetbase-php library
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright Copyright (c) Fleetbase Pte Ltd. <ron@fleetbase.io>
 * @license   http://opensource.org/licenses/MIT MIT
 */

declare(strict_types=1);

namespace Fleetbase\Sdk;

use Exception;
use Fleetbase\Sdk\HttpClient;
use Fleetbase\Sdk\Services\OrderService;

/**
 * Fleetbase PHP SDK
 */
class Fleetbase
{
    private string $version;
    private array $options;

    public function __construct(string $publicKey, array $config = [], bool $debug = false)
    {
        $this->version = $config['version'] ?? 'v1';
        $this->options = $options = [
            'version' => $this->version,
            'host' => $config['host'] ?? 'https://api.fleetbase.io',
            'namespace' => $config['namespace'] ?? $this->version,
            'debug' => $debug,
            'publicKey' => $publicKey
        ];

        if (!is_string($publicKey) && count($publicKey) === 0) {
            throw new Exception('⚠️ Invalid public key given to Fleetbase SDK');
        }

        $this->client = $client = new HttpClient($options);
        $this->orders = new OrderService($client);
        $this->entities = new Service('Entity', $client);
        $this->places = new Service('Place', $client);
        $this->drivers = new Service('Driver', $client);
        $this->vehicles = new Service('Vehicle', $client);
        $this->vendors = new Service('Vendor', $client);
        $this->contacts = new Service('Contact', $client);
        $this->serviceAreas = new Service('ServiceArea', $client);
        $this->zones = new Service('Zone', $client);
        $this->trackingStatuses = new Service('TrackingStatues', $client);
        $this->serviceRates = new Service('ServiceRate', $client);
        $this->serviceQuotes = new Service('ServiceQuote', $client);
    }

    public function setApiKey(string $publicKey): Fleetbase
    {
        return static::newInstance($publicKey);
    }

    public static function newInstance(): Fleetbase
    {
        $args = func_get_args();
        return new static(...$args);
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function getOptions(): array
    {
        return $this->options;
    }
}
