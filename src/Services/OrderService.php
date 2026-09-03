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

namespace Fleetbase\Sdk\Services;

use Fleetbase\Sdk\HttpClient;
use Fleetbase\Sdk\Service;
use Fleetbase\Sdk\Services\Concerns\OrderServiceEndpoints;

/**
 * Fleetbase PHP SDK Base Resource
 */
class OrderService extends Service
{
    use OrderServiceEndpoints;

    public function __construct(HttpClient $client, array $options = [])
    {
        parent::__construct('Order', $client, $options);
    }

    /**
     * @param string $id
     * @param array<string, mixed> $params
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function getDistanceAndTime($id, $params = [], $options = [])
    {
        if (!is_array($params) || !is_array($options)) {
            throw new \InvalidArgumentException('Order action parameters and options must be arrays.');
        }
        $legacyOptionKeys = ['onBefore', 'onAfter', 'headers', 'timeout', 'connect_timeout', 'verify', 'proxy'];
        if (array_intersect($legacyOptionKeys, array_keys($params)) !== []) {
            $options = array_merge($params, $options);
            $params = [];
        }
        $uri = $this->uriForResource($id, 'distance-and-time');

        return $this->client->get($uri, $params, $options);
    }

    /**
     * @param string $id
     * @param array<string, mixed> $params
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function getNextActivity($id, $params = [], $options = [])
    {
        $uri = $this->uriForResource($id, 'next-activity');

        return $this->client->get($uri, $params, $options);
    }

    /**
     * @param string $id
     * @param array<string, mixed> $params
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function dispatch($id, $params = [], $options = [])
    {
        if (!is_array($params) || !is_array($options)) {
            throw new \InvalidArgumentException('Order action parameters and options must be arrays.');
        }

        return $this->dispatchOrder($id, $params, $options);
    }

    /**
     * @param string $id
     * @param array<string, mixed> $params
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function start($id, $params = [], $options = [])
    {
        $uri = $this->uriForResource($id, 'start');

        return $this->client->post($uri, $params, $options);
    }

    /**
     * @param string $id
     * @param array<string, mixed> $params
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function updateActivity($id, $params = [], $options = [])
    {
        $uri = $this->uriForResource($id, 'update-activity');

        return $this->client->post($uri, $params, $options);
    }

    /**
     * @param string $id
     * @param string $destinationId
     * @param array<string, mixed> $params
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function setDestination($id, $destinationId, $params = [], $options = [])
    {
        $uri = $this->uriForResource($id, 'set-destination/' . rawurlencode((string) $destinationId));

        return $this->client->patch($uri, $params, $options);
    }

    /**
     * @param string $id
     * @param string|null $subjectId
     * @param array<string, mixed> $params
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function captureQrCode($id, $subjectId = null, $params = [], $options = [])
    {
        $path = 'capture-qr' . ($subjectId !== null ? '/' . rawurlencode((string) $subjectId) : '');
        $uri = $this->uriForResource($id, $path);

        return $this->client->post($uri, $params, $options);
    }

    /**
     * @param string $id
     * @param string|null $subjectId
     * @param array<string, mixed> $params
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function captureSignature($id, $subjectId = null, $params = [], $options = [])
    {
        $path = 'capture-signature' . ($subjectId !== null ? '/' . rawurlencode((string) $subjectId) : '');
        $uri = $this->uriForResource($id, $path);

        return $this->client->post($uri, $params, $options);
    }

    /**
     * @param string $id
     * @param array<string, mixed> $params
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function complete($id, $params = [], $options = [])
    {
        $uri = $this->uriForResource($id, 'complete');

        return $this->client->post($uri, $params, $options);
    }

    /**
     * @param string $id
     * @param array<string, mixed> $params
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function cancel($id, $params = [], $options = [])
    {
        $uri = $this->uriForResource($id, 'cancel');

        return $this->client->delete($uri, $params, $options);
    }
}
