<?php

/**
 * This file is part of the fleetbase/fleetbase-php library.
 *
 * @copyright Copyright (c) Fleetbase Pte Ltd. <ron@fleetbase.io>
 * @license https://www.gnu.org/licenses/agpl-3.0.html AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace Fleetbase\Sdk;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use JsonSerializable;
use Traversable;

/** @implements IteratorAggregate<int, Resource> */
final class Collection implements IteratorAggregate, Countable, JsonSerializable
{
    /** @var array<int, Resource> */
    private array $items;

    /** @var array<string, mixed> */
    private array $meta;

    /** @var array<string, mixed> */
    private array $links;

    /**
     * @param array<int, Resource> $items
     * @param array<string, mixed> $meta
     * @param array<string, mixed> $links
     */
    public function __construct(array $items, array $meta = [], array $links = [])
    {
        $this->items = array_values($items);
        $this->meta = $meta;
        $this->links = $links;
    }

    /** @return array<int, Resource> */
    public function all(): array
    {
        return $this->items;
    }

    /** @return array<string, mixed> */
    public function meta(): array
    {
        return $this->meta;
    }

    /** @return array<string, mixed> */
    public function links(): array
    {
        return $this->links;
    }

    /** @return Traversable<int, Resource> */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    public function count(): int
    {
        return count($this->items);
    }

    /** @param mixed $offset */
    public function offsetExists($offset): bool
    {
        return is_int($offset) && isset($this->items[$offset]);
    }

    /** @param mixed $offset */
    public function offsetGet($offset): ?Resource
    {
        return is_int($offset) ? ($this->items[$offset] ?? null) : null;
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value): void
    {
        if (!$value instanceof Resource) {
            throw new \InvalidArgumentException('Fleetbase collections accept Resource instances only.');
        }

        if ($offset === null) {
            $this->items[] = $value;
            return;
        }

        if (!is_int($offset)) {
            throw new \InvalidArgumentException('Fleetbase collection offsets must be integers.');
        }

        $this->items[$offset] = $value;
    }

    /** @param mixed $offset */
    public function offsetUnset($offset): void
    {
        if (is_int($offset)) {
            unset($this->items[$offset]);
            $this->items = array_values($this->items);
        }
    }

    /** @return array{data: array<int, array<string, mixed>>, meta: array<string, mixed>, links: array<string, mixed>} */
    public function jsonSerialize(): array
    {
        return [
            'data' => array_map(static function (Resource $resource): array {
                return $resource->toArray();
            }, $this->items),
            'meta' => $this->meta,
            'links' => $this->links,
        ];
    }
}
