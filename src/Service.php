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

/**
 * Fleetbase PHP SDK Base Service
 */
class Service
{
    private string $resource;
    private string $namespace;
    private array $options = [];
    private HttpClient $client;

    public function __construct(string $resource, HttpClient $client, array $options = [])
    {
        $this->resource = $resource;
        $this->namespace = Utils::createNamespace($resource);
        $this->client = $client;
        $this->options = $options;
    }

    private function resolve($data)
    {
        $class = "Fleetbase\\Sdk\\Resources\\" . Utils::classify($this->resource);
        return new $class((array) $data, $this);
    }

    public function uri(?string $path = null)
    {
        return $this->namespace . ($path ? '/' . $path : '');
    }

    public function uriForResource(string $id, ?string $path = null)
    {
        return $this->namespace . '/' . $id . ($path ? '/' . $path : '');
    }

    public function create(array $attributes = [], array $options = [])
    {
        $uri = $this->uri();
        $data = $this->client->post($uri, $attributes, $options);

        return $this->resolve($data);
    }

    public function update(string $id, array $attributes = [], array $options = [])
    {
        $uri = $this->uri($id);
        $data = $this->client->put($uri, $attributes, $options);

        return $this->resolve($data);
    }

    public function findRecord(string $id, array $options = [])
    {
        $uri = $this->uri($id);
        $data = $this->client->get($uri, [], $options);

        return $this->resolve($data);
    }

    public function findAll(array $options = [])
    {
        $uri = $this->uri();
        $data = $this->client->get($uri, [], $options);

        if (is_array($data)) {
            return array_map(
                function ($item) {
                    return $this->resolve($item);
                },
                $data
            );
        }

        return $data;
    }

    public function query(array $query = [], array $options = [])
    {
        $uri = $this->uri();
        $data = $this->client->get($uri, $query, $options);

        if (is_array($data)) {
            return array_map(
                function ($item) {
                    return $this->resolve($item);
                },
                $data
            );
        }

        return $data;
    }

    public function queryRecord(array $query = [], array $options = [])
    {
        $query['single'] = true;

        $uri = $this->uri();
        $data = $this->client->get($uri, $query, $options);

        return $this->resolve($data);
    }

    public function destroy($id, array $options = [])
    {
        if ($id instanceof Resource) {
            $id = $id->getAttribute('id');
        }

        $uri = $this->uri($id);
        $data = $this->client->delete($uri, [], $options);

        return $this->resolve($data);
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function getClient(): HttpClient
    {
        return $this->client;
    }
}
