<?php

/**
 * This file is part of the fleetbase/fleetbase-php library.
 *
 * @copyright Copyright (c) Fleetbase Pte Ltd. <ron@fleetbase.io>
 * @license https://www.gnu.org/licenses/agpl-3.0.html AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace Fleetbase\Sdk;

use Fleetbase\Sdk\Exception\InvalidConfigurationException;

final class Configuration
{
    private string $apiKey;
    private string $host;
    private string $namespace;
    private string $version;
    private bool $debug;

    /** @var array<string, mixed> */
    private array $options;

    /** @param array<string, mixed> $options */
    public function __construct(string $apiKey, array $options = [], bool $debug = false)
    {
        if (trim($apiKey) === '') {
            throw new InvalidConfigurationException('A non-empty Fleetbase API key is required.');
        }

        $version = $options['version'] ?? 'v1';
        $host = $options['host'] ?? 'https://api.fleetbase.io';
        $namespace = $options['namespace'] ?? $version;

        if (!is_string($version) || trim($version) === '') {
            throw new InvalidConfigurationException('Fleetbase API version must be a non-empty string.');
        }
        if (!is_string($host) || filter_var($host, FILTER_VALIDATE_URL) === false) {
            throw new InvalidConfigurationException('Fleetbase host must be an absolute HTTP(S) URL.');
        }
        if (!in_array(strtolower((string) parse_url($host, PHP_URL_SCHEME)), ['http', 'https'], true)) {
            throw new InvalidConfigurationException('Fleetbase host must use HTTP or HTTPS.');
        }
        if (!is_string($namespace)) {
            throw new InvalidConfigurationException('Fleetbase namespace must be a string.');
        }

        $this->apiKey = $apiKey;
        $this->host = rtrim($host, '/');
        $this->namespace = trim($namespace, '/');
        $this->version = $version;
        $this->debug = $debug;
        $this->options = $options;
    }

    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getNamespace(): string
    {
        return $this->namespace;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function isDebug(): bool
    {
        return $this->debug;
    }

    public function getBaseUri(): string
    {
        return $this->host . ($this->namespace !== '' ? '/' . $this->namespace : '') . '/';
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_merge($this->options, [
            'version' => $this->version,
            'host' => $this->host,
            'namespace' => $this->namespace,
            'debug' => $this->debug,
            'publicKey' => $this->apiKey,
        ]);
    }
}
