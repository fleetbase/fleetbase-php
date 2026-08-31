<?php

/**
 * This file is part of the fleetbase/fleetbase-php library
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright Copyright (c) Fleetbase Pte Ltd. <ron@fleetbase.io>
 * @license   https://www.gnu.org/licenses/agpl-3.0.html AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace Fleetbase\Sdk\Resources;

use Fleetbase\Sdk\Resource;
use Fleetbase\Sdk\Services\OrderService;

/**
 * Fleetbase PHP SDK Base Resource
 */
class Order extends Resource
{
    /**
     * @param array<string, mixed> $attributes
     * @param array<string, mixed> $options
     * @return void
     */
    public function __constructor(array $attributes = [], ?OrderService $service = null, array $options = [])
    {
        parent::__construct($attributes, $service, $options);
    }

    /**
     * @param array<string, mixed> $params
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function getDistanceAndTime($params = [], $options = [])
    {
        return $this->orderService()->getDistanceAndTime($this->resourceId(), $params, $options);
    }

    /**
     * @param array<string, mixed> $params
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function getNextActivity($params = [], $options = [])
    {
        return $this->orderService()->getNextActivity($this->resourceId(), $params, $options);
    }

    /**
     * @param array<string, mixed> $params
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function dispatch($params = [], $options = [])
    {
        return $this->orderService()->dispatch($this->resourceId(), $params, $options);
    }

    /**
     * @param array<string, mixed> $params
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function start($params = [], $options = [])
    {
        return $this->orderService()->start($this->resourceId(), $params, $options);
    }

    /**
     * @param array<string, mixed> $params
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function updateActivity($params = [], $options = [])
    {
        return $this->orderService()->updateActivity($this->resourceId(), $params, $options);
    }

    /**
     * @param string $destinationId
     * @param array<string, mixed> $params
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function setDestination($destinationId, $params = [], $options = [])
    {
        return $this->orderService()->setDestination($this->resourceId(), $destinationId, $params, $options);
    }

    /**
     * @param string|null $subjectId
     * @param array<string, mixed> $params
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function captureQrCode($subjectId = null, $params = [], $options = [])
    {
        return $this->orderService()->captureQrCode($this->resourceId(), $subjectId, $params, $options);
    }

    /**
     * @param string|null $subjectId
     * @param array<string, mixed> $params
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function captureSignature($subjectId = null, $params = [], $options = [])
    {
        return $this->orderService()->captureSignature($this->resourceId(), $subjectId, $params, $options);
    }

    /**
     * @param array<string, mixed> $params
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function complete($params = [], $options = [])
    {
        return $this->orderService()->complete($this->resourceId(), $params, $options);
    }

    /**
     * @param array<string, mixed> $params
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function cancel($params = [], $options = [])
    {
        return $this->orderService()->cancel($this->resourceId(), $params, $options);
    }

    private function orderService(): OrderService
    {
        if (!$this->service instanceof OrderService) {
            throw new \LogicException('This order is not attached to an OrderService.');
        }

        return $this->service;
    }

    private function resourceId(): string
    {
        $id = $this->getAttribute('id');
        if (!is_string($id) || $id === '') {
            throw new \LogicException('This order does not have an ID.');
        }

        return $id;
    }
}
