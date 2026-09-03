<?php

/**
 * Generated from the locked Fleetbase Postman contract.
 * Do not edit by hand; run tools/generate-endpoint-services.php.
 *
 * @license https://www.gnu.org/licenses/agpl-3.0.html AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace Fleetbase\Sdk\Services\Concerns;

trait DeviceServiceEndpoints
{
    /**
     * Attach Device.
     *
     * @param scalar|\Fleetbase\Sdk\Resource|array<string, mixed> $parameters First path value, or the legacy endpoint envelope.
     * @param mixed $options Request data, a second path value, or legacy request options.
     * @param array<string, mixed> $requestOptions
     * @return mixed
     */
    public function attachDevice($parameters = [], $options = [], $requestOptions = [])
    {
        return $this->endpointFromArguments('POST', '{{base_url}}/{{namespace}}/devices/{{device_id}}/attach', ['device_id'], 'body', func_get_args());
    }

    /**
     * Create a Device.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function createDevice(array $parameters = [], array $options = [])
    {
        return $this->endpointFromArguments('POST', '{{base_url}}/{{namespace}}/devices', [], 'body', func_get_args());
    }

    /**
     * Delete a Device.
     *
     * @param scalar|\Fleetbase\Sdk\Resource|array<string, mixed> $parameters First path value, or the legacy endpoint envelope.
     * @param mixed $options Request data, a second path value, or legacy request options.
     * @param array<string, mixed> $requestOptions
     * @return mixed
     */
    public function deleteDevice($parameters = [], $options = [], $requestOptions = [])
    {
        return $this->endpointFromArguments('DELETE', '{{base_url}}/{{namespace}}/devices/{{device_id}}', ['device_id'], 'body', func_get_args());
    }

    /**
     * Detach Device.
     *
     * @param scalar|\Fleetbase\Sdk\Resource|array<string, mixed> $parameters First path value, or the legacy endpoint envelope.
     * @param mixed $options Request data, a second path value, or legacy request options.
     * @param array<string, mixed> $requestOptions
     * @return mixed
     */
    public function detachDevice($parameters = [], $options = [], $requestOptions = [])
    {
        return $this->endpointFromArguments('POST', '{{base_url}}/{{namespace}}/devices/{{device_id}}/detach', ['device_id'], 'body', func_get_args());
    }

    /**
     * Query Devices.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function queryDevices(array $parameters = [], array $options = [])
    {
        return $this->endpointFromArguments('GET', '{{base_url}}/{{namespace}}/devices', [], 'query', func_get_args());
    }

    /**
     * Retrieve a Device.
     *
     * @param scalar|\Fleetbase\Sdk\Resource|array<string, mixed> $parameters First path value, or the legacy endpoint envelope.
     * @param mixed $options Request data, a second path value, or legacy request options.
     * @param array<string, mixed> $requestOptions
     * @return mixed
     */
    public function retrieveDevice($parameters = [], $options = [], $requestOptions = [])
    {
        return $this->endpointFromArguments('GET', '{{base_url}}/{{namespace}}/devices/{{device_id}}', ['device_id'], 'query', func_get_args());
    }

    /**
     * Update a Device.
     *
     * @param scalar|\Fleetbase\Sdk\Resource|array<string, mixed> $parameters First path value, or the legacy endpoint envelope.
     * @param mixed $options Request data, a second path value, or legacy request options.
     * @param array<string, mixed> $requestOptions
     * @return mixed
     */
    public function updateDevice($parameters = [], $options = [], $requestOptions = [])
    {
        return $this->endpointFromArguments('PUT', '{{base_url}}/{{namespace}}/devices/{{device_id}}', ['device_id'], 'body', func_get_args());
    }
}
