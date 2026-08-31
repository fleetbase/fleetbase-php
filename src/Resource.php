<?php

/**
 * This file is part of the fleetbase/fleetbase-php library.
 *
 * @copyright Copyright (c) Fleetbase Pte Ltd. <ron@fleetbase.io>
 * @license https://www.gnu.org/licenses/agpl-3.0.html AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace Fleetbase\Sdk;

use JsonSerializable;

class Resource implements JsonSerializable
{
    /** @var array<string, mixed> */
    protected array $attributes = [];

    /** @var array<string, mixed> */
    protected array $originalAttributes = [];

    /** @var array<string, mixed> */
    protected array $options = [];

    /** @var array<string, mixed> */
    private array $dirtyAttributes = [];

    /** @var array<string, array{old: mixed, new: mixed}> */
    private array $changes = [];

    private bool $isSaving = false;
    private bool $isLoading = false;
    private bool $isDestroying = false;
    private bool $isReloading = false;
    protected ?Service $service;

    /**
     * @param array<string, mixed> $attributes
     * @param array<string, mixed>|null $options
     */
    public function __construct(array $attributes = [], ?Service $service = null, ?array $options = [])
    {
        $this->attributes = $attributes;
        $this->originalAttributes = $attributes;
        $this->service = $service;
        $this->options = $options ?? [];
    }

    /** @return mixed */
    public function __get(string $name)
    {
        if ($name === 'isDestroyed') {
            return ($this->attributes['deleted'] ?? false) === true;
        }
        if (in_array($name, ['isSaving', 'isLoading', 'isDestroying', 'isReloading'], true)) {
            return $this->{$name};
        }

        return $this->getAttribute($name);
    }

    /** @param mixed $value */
    public function __set(string $name, $value): void
    {
        $this->setAttribute($name, $value);
    }

    public function __isset(string $name): bool
    {
        return $this->hasAttribute($name);
    }

    /**
     * @param array<string, mixed> $attributes
     * @param array<string, mixed> $options
     * @return Resource
     */
    public function create($attributes = [], $options = [])
    {
        $service = $this->requireService();
        $this->isSaving = true;
        $options = $this->withAfterHook($options, function ($response): void {
            $this->resetAttributes($this->stringKeyedArray((array) $response));
        });

        try {
            return $service->create(is_array($attributes) ? $attributes : [], $options);
        } finally {
            $this->isSaving = false;
        }
    }

    /**
     * @param array<string, mixed> $attributes
     * @param array<string, mixed> $options
     * @return Resource
     */
    public function update($attributes = [], $options = [])
    {
        $service = $this->requireService();
        $id = $this->requireId();
        $this->isSaving = true;
        $options = $this->withAfterHook($options, function ($response): void {
            $this->resetAttributes($this->stringKeyedArray((array) $response));
        });

        try {
            return $service->update($id, is_array($attributes) ? $attributes : [], $options);
        } finally {
            $this->isSaving = false;
        }
    }

    /**
     * @param array<string, mixed> $options
     * @return Resource
     */
    public function destroy($options = [])
    {
        $service = $this->requireService();
        $id = $this->requireId();
        $this->isDestroying = true;
        $options = $this->withAfterHook($options, function ($response): void {
            $attributes = $this->stringKeyedArray((array) $response);
            if (!array_key_exists('deleted', $attributes)) {
                $attributes['deleted'] = true;
            }
            if (!array_key_exists('id', $attributes)) {
                $attributes['id'] = $this->getAttribute('id');
            }
            $this->resetAttributes($attributes);
        });

        try {
            return $service->destroy($id, $options);
        } finally {
            $this->isDestroying = false;
        }
    }

    /**
     * @param array<string, mixed>|null $options
     * @return Resource
     */
    public function save(?array $options = [])
    {
        $options ??= [];
        if ($this->getAttribute('id') === null) {
            return $this->create($this->getAttributes(), $options);
        }

        $attributes = $this->dirtyAttributes !== [] ? $this->dirtyAttributes : $this->getAttributes();
        return $this->update($attributes, $options);
    }

    /** @param array<string, mixed> $options */
    public function reload(array $options = []): Resource
    {
        $this->isReloading = true;
        try {
            $resource = $this->requireService()->findRecord($this->requireId(), $options);
            if (!$resource instanceof Resource) {
                throw new \UnexpectedValueException('Fleetbase reload did not return a resource.');
            }
            $this->resetAttributes($resource->toArray());
            return $this;
        } finally {
            $this->isReloading = false;
        }
    }

    /**
     * @param string|array<int, string> $attribute
     * @param mixed $defaultValue
     * @return mixed
     */
    public function getAttribute($attribute, $defaultValue = null)
    {
        if (is_array($attribute)) {
            return $this->getAttributes($attribute);
        }
        if (!is_string($attribute)) {
            return $defaultValue;
        }

        return Utils::get($this->attributes, $attribute, $defaultValue);
    }

    /** @param mixed $value */
    public function setAttribute(string $attribute, $value): Resource
    {
        $old = $this->attributes[$attribute] ?? null;
        $this->attributes[$attribute] = $value;

        if (!array_key_exists($attribute, $this->originalAttributes) || $this->originalAttributes[$attribute] !== $value) {
            $this->dirtyAttributes[$attribute] = $value;
            $this->changes[$attribute] = ['old' => $old, 'new' => $value];
        } else {
            unset($this->dirtyAttributes[$attribute], $this->changes[$attribute]);
        }

        return $this;
    }

    /**
     * @param string|array<int, string> $property
     * @return bool
     */
    public function hasAttribute($property)
    {
        if (is_array($property)) {
            return Arr::every($property, function ($item): bool {
                return is_string($item) && $this->hasAttribute($item);
            });
        }

        return is_string($property) && array_key_exists($property, $this->attributes);
    }

    /**
     * @param string|array<int, string> $property
     * @return bool
     */
    public function isAttributeFilled($property)
    {
        if (is_array($property)) {
            return $this->hasAttribute($property) && Arr::every($property, function ($item): bool {
                return is_string($item) && $this->getAttribute($item) !== null && $this->getAttribute($item) !== '';
            });
        }

        return $this->hasAttribute($property)
            && $this->getAttribute($property) !== null
            && $this->getAttribute($property) !== '';
    }

    /**
     * @param array<int, string>|null $properties
     * @return array<string, mixed>
     */
    public function getAttributes(?array $properties = [])
    {
        if ($properties === null || $properties === []) {
            return $this->attributes;
        }

        $attributes = [];
        foreach ($properties as $property) {
            $value = $this->getAttribute($property);
            if ($value instanceof self) {
                $value = $value->toArray();
            }
            $attributes[$property] = $value;
        }

        return $attributes;
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        $values = [];
        foreach ($this->attributes as $key => $value) {
            $values[$key] = $this->normalizeValue($value);
        }

        return $values;
    }

    /** @return array<string, array{old: mixed, new: mixed}> */
    public function getChanges(): array
    {
        return $this->changes;
    }

    /** @return array<string, mixed> */
    public function getDirtyAttributes(): array
    {
        return $this->dirtyAttributes;
    }

    /**
     * @param string|array<int, string>|null $attribute
     * @return bool
     */
    public function isDirty($attribute)
    {
        if ($attribute === null) {
            return $this->dirtyAttributes !== [];
        }
        if (is_array($attribute)) {
            return Arr::any($attribute, function ($item): bool {
                return is_string($item) && array_key_exists($item, $this->dirtyAttributes);
            });
        }

        return is_string($attribute) && array_key_exists($attribute, $this->dirtyAttributes);
    }

    public function getService(): ?Service
    {
        return $this->service;
    }

    /** @param mixed $offset */
    public function offsetExists($offset): bool
    {
        return is_string($offset) && $this->hasAttribute($offset);
    }

    /**
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return is_string($offset) ? $this->getAttribute($offset) : null;
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value): void
    {
        if (!is_string($offset) || $offset === '') {
            throw new \InvalidArgumentException('Resource attribute names must be non-empty strings.');
        }
        $this->setAttribute($offset, $value);
    }

    /** @param mixed $offset */
    public function offsetUnset($offset): void
    {
        if (!is_string($offset) || !array_key_exists($offset, $this->attributes)) {
            return;
        }
        $old = $this->attributes[$offset];
        unset($this->attributes[$offset]);
        $this->dirtyAttributes[$offset] = null;
        $this->changes[$offset] = ['old' => $old, 'new' => null];
    }

    /** @return array<string, mixed> */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    protected function requireService(): Service
    {
        if (!$this->service instanceof Service) {
            throw new \LogicException('This resource is not attached to a Fleetbase service.');
        }

        return $this->service;
    }

    private function requireId(): string
    {
        $id = $this->getAttribute('id');
        if (!is_string($id) || $id === '') {
            throw new \LogicException('This Fleetbase resource does not have an ID.');
        }

        return $id;
    }

    /** @param array<string, mixed> $attributes */
    private function resetAttributes(array $attributes): void
    {
        $this->attributes = $attributes;
        $this->originalAttributes = $attributes;
        $this->dirtyAttributes = [];
        $this->changes = [];
    }

    /**
     * @param mixed $options
     * @return array<string, mixed>
     */
    private function withAfterHook($options, callable $resourceHook): array
    {
        if (!is_array($options)) {
            throw new \InvalidArgumentException('Resource options must be an array.');
        }
        $consumerHook = $options['onAfter'] ?? null;
        $options['onAfter'] = static function ($response) use ($resourceHook, $consumerHook): void {
            $resourceHook($response);
            if (is_callable($consumerHook)) {
                call_user_func($consumerHook, $response);
            }
        };

        $normalized = [];
        foreach ($options as $key => $value) {
            if (is_string($key)) {
                $normalized[$key] = $value;
            }
        }

        return $normalized;
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    private function normalizeValue($value)
    {
        if ($value instanceof self) {
            return $value->toArray();
        }
        if (is_array($value)) {
            $values = [];
            foreach ($value as $key => $item) {
                $values[$key] = $this->normalizeValue($item);
            }
            return $values;
        }
        if ($value instanceof JsonSerializable) {
            return $value->jsonSerialize();
        }

        return $value;
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
