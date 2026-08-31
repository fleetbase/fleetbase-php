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

namespace Fleetbase\Sdk\Services;

use Fleetbase\Sdk\Service;
use Fleetbase\Sdk\HttpClient;

/**
 * Fleetbase PHP SDK Base Resource
 */
class OrderService extends Service
{
    public function __construct(HttpClient $client, array $options = [])
    {
        parent::__construct('Order', $client, $options);
    }

    public function getDistanceAndTime($id, $params = [], $options = [])
    {
        $uri = $this->uriForResource($id, 'distance-and-time');

        return $this->client->get($uri, $params, $options);
    }

    public function getNextActivity($id, $params = [], $options = [])
    {
        $uri = $this->uriForResource($id, 'next-activity');

        return $this->client->get($uri, $params, $options);
    }

    public function dispatch($id, $params = [], $options = [])
    {
        $uri = $this->uriForResource($id, 'dispatch');

        return $this->client->post($uri, $params, $options);
    }

    public function start($id, $params = [], $options = [])
    {
        $uri = $this->uriForResource($id, 'start');

        return $this->client->post($uri, $params, $options);
    }

    public function updateActivity($id, $params = [], $options = [])
    {
        $uri = $this->uriForResource($id, 'update-activity');

        return $this->client->post($uri, $params, $options);
    }

    public function setDestination($id, $destinationId, $params = [], $options = [])
    {
        $uri = $this->uriForResource($id, 'set-destination/' . $destinationId);

        return $this->client->post($uri, $params, $options);
    }

    public function captureQrCode($id, $subjectId = null, $params = [], $options = [])
    {
        $uri = $this->uriForResource($id, 'capture-qr' . $subjectId ? '' : '/' . $subjectId);

        return $this->client->post($uri, $params, $options);
    }

    public function captureSignature($id, $subjectId = null, $params = [], $options = [])
    {
        $uri = $this->uriForResource($id, 'capture-signature' . $subjectId ? '' : '/' . $subjectId);

        return $this->client->post($uri, $params, $options);
    }

    public function complete($id, $params = [], $options = [])
    {
        $uri = $this->uriForResource($id, 'complete');

        return $this->client->post($uri, $params, $options);
    }

    public function cancel($id, $params = [], $options = [])
    {
        $uri = $this->uriForResource($id, 'cancel');

        return $this->client->delete($uri, $params, $options);
    }
}
