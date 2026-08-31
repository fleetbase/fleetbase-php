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

namespace Fleetbase\Sdk\Resources;

use Fleetbase\Sdk\Resource;
use Fleetbase\Sdk\Services\OrderService;

/**
 * Fleetbase PHP SDK Base Resource
 */
class Order extends Resource
{
    public function __constructor(array $attributes = [], ?OrderService $service = null, array $options = [])
    {
        parent::__construct($attributes, $service, $options);
    }

    public function getDistanceAndTime($params = [], $options = [])
    {
        return $this->service->getDistanceAndTime($this->id, $params, $options);
    }

    public function getNextActivity($params = [], $options = [])
    {
        return $this->service->getNextActivity($this->id, $params, $options);
    }

    public function dispatch($params = [], $options = [])
    {
        return $this->service->dispatch($this->id, $params, $options);
    }

    public function start($params = [], $options = [])
    {
        return $this->service->start($this->id, $params, $options);
    }

    public function updateActivity($params = [], $options = [])
    {
        return $this->service->updateActivity($this->id, $params, $options);
    }

    public function setDestination($destinationId, $params = [], $options = [])
    {
        return $this->service->setDestination($this->id, $destinationId, $params, $options);
    }

    public function captureQrCode($subjectId = null, $params = [], $options = [])
    {
        return $this->service->captureQrCode($this->id, $subjectId, $params, $options);
    }

    public function captureSignature($subjectId = null, $params = [], $options = [])
    {
        return $this->service->captureSignature($this->id, $subjectId, $params, $options);
    }

    public function complete($params = [], $options = [])
    {
        return $this->service->complete($this->id, $params, $options);
    }

    public function cancel($params = [], $options = [])
    {
        return $this->service->cancel($this->id, $params, $options);
    }
}
