<?php

/**
 * This file is part of the fleetbase/fleetbase-php library.
 *
 * @copyright Copyright (c) Fleetbase Pte Ltd. <ron@fleetbase.io>
 * @license https://www.gnu.org/licenses/agpl-3.0.html AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace Fleetbase\Sdk;

class Service
{
    protected string $resource;
    protected string $namespace;

    /** @var array<string, mixed> */
    protected array $options = [];

    protected HttpClient $client;

    /** @param array<string, mixed> $options */
    public function __construct(string $resource, HttpClient $client, array $options = [])
    {
        $this->resource = $resource;
        $namespace = $options['namespace'] ?? null;
        $this->namespace = is_string($namespace)
            ? trim($namespace, '/')
            : Utils::createNamespace($resource);
        $this->client = $client;
        $this->options = $options;
    }

    /** @return string */
    public function uri(?string $path = null)
    {
        return $this->namespace . ($path !== null && $path !== '' ? '/' . ltrim($path, '/') : '');
    }

    /** @return string */
    public function uriForResource(string $id, ?string $path = null)
    {
        return $this->uri(rawurlencode($id) . ($path !== null && $path !== '' ? '/' . ltrim($path, '/') : ''));
    }

    /**
     * @param array<string, mixed> $attributes
     * @param array<string, mixed> $options
     * @return Resource
     */
    public function create(array $attributes = [], array $options = [])
    {
        return $this->resolve($this->client->post($this->uri(), $attributes, $options));
    }

    /**
     * @param array<string, mixed> $attributes
     * @param array<string, mixed> $options
     * @return Resource
     */
    public function update(string $id, array $attributes = [], array $options = [])
    {
        return $this->resolve($this->client->put($this->uriForResource($id), $attributes, $options));
    }

    /**
     * @param array<string, mixed> $options
     * @return Resource
     */
    public function findRecord(string $id, array $options = [])
    {
        return $this->resolve($this->client->get($this->uriForResource($id), [], $options));
    }

    /**
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function findAll(array $options = [])
    {
        $data = $this->client->get($this->uri(), [], $options);
        $collection = $this->resolveCollection($data);

        return is_array($collection) ? $collection : $data;
    }

    /**
     * @param array<string, mixed> $query
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function query(array $query = [], array $options = [])
    {
        $data = $this->client->get($this->uri(), $query, $options);
        $collection = $this->resolveCollection($data);

        return is_array($collection) ? $collection : $data;
    }

    /**
     * @param array<string, mixed> $query
     * @param array<string, mixed> $options
     * @return Resource
     */
    public function queryRecord(array $query = [], array $options = [])
    {
        $query['single'] = true;
        return $this->resolve($this->client->get($this->uri(), $query, $options));
    }

    /**
     * @param Resource|string $id
     * @param array<string, mixed> $options
     * @return Resource
     */
    public function destroy($id, array $options = [])
    {
        if ($id instanceof Resource) {
            $id = $id->getAttribute('id');
        }
        if (!is_string($id) || $id === '') {
            throw new \InvalidArgumentException('A resource ID is required for deletion.');
        }

        return $this->resolve($this->client->delete($this->uriForResource($id), [], $options));
    }

    /**
     * Call an explicit non-CRUD endpoint without bypassing client behavior.
     *
     * @param array<string, mixed> $data
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function action(string $method, string $path, array $data = [], array $options = [])
    {
        return $this->client->request($method, $this->uri($path), $data, $options);
    }

    /**
     * Execute an endpoint copied from the locked official API contract.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    protected function endpoint(string $method, string $template, array $parameters = [], array $options = [])
    {
        $path = $template;
        $prefix = '{{base_url}}/{{namespace}}';
        if (strncasecmp($path, $prefix, strlen($prefix)) === 0) {
            $path = ltrim(substr($path, strlen($prefix)), '/');
        }

        $used = [];
        foreach (['/\{\{([^}]+)\}\}/', '/:([A-Za-z][A-Za-z0-9_-]*)/'] as $pattern) {
            while (preg_match($pattern, $path, $matches) === 1) {
                $placeholder = $matches[0];
                $name = $matches[1];
                if (!array_key_exists($name, $parameters) || !is_scalar($parameters[$name])) {
                    throw new \InvalidArgumentException(sprintf('Endpoint parameter "%s" is required.', $name));
                }
                $used[$name] = true;
                $path = str_replace($placeholder, rawurlencode((string) $parameters[$name]), $path);
            }
        }

        $data = [];
        if (isset($parameters['body']) && is_array($parameters['body'])) {
            $data = $this->stringKeyedArray($parameters['body']);
        } else {
            foreach ($parameters as $name => $value) {
                if ($name !== 'body' && !isset($used[$name])) {
                    $data[$name] = $value;
                }
            }
        }

        if (isset($options['query']) && is_array($options['query'])) {
            $query = http_build_query($options['query'], '', '&', PHP_QUERY_RFC3986);
            if ($query !== '') {
                $path .= (strpos($path, '?') === false ? '?' : '&') . $query;
            }
            unset($options['query']);
        }

        return $this->client->request($method, $path, $data, $options);
    }

    /** @return array<string, mixed> */
    public function getOptions(): array
    {
        return $this->options;
    }

    public function getClient(): HttpClient
    {
        return $this->client;
    }

    /** @param mixed $data */
    protected function resolve($data): Resource
    {
        if (is_object($data) && isset($data->data) && is_object($data->data)) {
            $data = $data->data;
        }
        if (!is_object($data) && !is_array($data)) {
            throw new \UnexpectedValueException('Fleetbase resource responses must be objects or arrays.');
        }

        $class = 'Fleetbase\\Sdk\\Resources\\' . Utils::classify($this->resource);
        if (!class_exists($class) || !is_subclass_of($class, Resource::class)) {
            $class = Resource::class;
        }

        /** @var Resource $resource */
        $resource = new $class($this->stringKeyedArray((array) $data), $this, $this->options);
        return $resource;
    }

    /**
     * @param mixed $data
     * @return array<int, Resource>|null
     */
    protected function resolveCollection($data): ?array
    {
        $items = null;

        if (is_array($data)) {
            $items = $data;
        } elseif (is_object($data)) {
            foreach (['data', 'results', 'items'] as $key) {
                if (isset($data->{$key}) && is_array($data->{$key})) {
                    $items = $data->{$key};
                    break;
                }
            }
        }

        if ($items === null) {
            return null;
        }

        $resources = [];
        foreach ($items as $item) {
            $resources[] = $this->resolve($item);
        }

        return $resources;
    }

    /**
     * @param array<mixed, mixed> $value
     * @return array<string, mixed>
     */
    private function stringKeyedArray(array $value): array
    {
        $result = [];
        foreach ($value as $key => $item) {
            if (is_string($key)) {
                $result[$key] = $item;
            }
        }

        return $result;
    }
}
