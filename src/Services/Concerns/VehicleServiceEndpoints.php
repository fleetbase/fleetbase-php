<?php

/**
 * Generated from the locked Fleetbase Postman contract.
 * Do not edit by hand; run tools/generate-endpoint-services.php.
 *
 * @license https://www.gnu.org/licenses/agpl-3.0.html AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace Fleetbase\Sdk\Services\Concerns;

trait VehicleServiceEndpoints
{
    /**
     * Create a Vehicle.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function createVehicle(array $parameters = [], array $options = [])
    {
        return $this->endpointFromArguments('POST', '{{base_url}}/{{namespace}}/vehicles', [], 'body', func_get_args());
    }

    /**
     * Delete a Vehicle.
     *
     * @param scalar|\Fleetbase\Sdk\Resource|array<string, mixed> $parameters First path value, or the legacy endpoint envelope.
     * @param mixed $options Request data, a second path value, or legacy request options.
     * @param array<string, mixed> $requestOptions
     * @return mixed
     */
    public function deleteVehicle($parameters = [], $options = [], $requestOptions = [])
    {
        return $this->endpointFromArguments('DELETE', '{{base_url}}/{{namespace}}/vehicles/:id', ['id'], 'body', func_get_args());
    }

    /**
     * Query Vehicles.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function queryVehicles(array $parameters = [], array $options = [])
    {
        return $this->endpointFromArguments('GET', '{{base_url}}/{{namespace}}/vehicles', [], 'query', func_get_args());
    }

    /**
     * Retrieve a Vehicle.
     *
     * @param scalar|\Fleetbase\Sdk\Resource|array<string, mixed> $parameters First path value, or the legacy endpoint envelope.
     * @param mixed $options Request data, a second path value, or legacy request options.
     * @param array<string, mixed> $requestOptions
     * @return mixed
     */
    public function retrieveVehicle($parameters = [], $options = [], $requestOptions = [])
    {
        return $this->endpointFromArguments('GET', '{{base_url}}/{{namespace}}/vehicles/:id', ['id'], 'query', func_get_args());
    }

    /**
     * Track Vehicle.
     *
     * @param scalar|\Fleetbase\Sdk\Resource|array<string, mixed> $parameters First path value, or the legacy endpoint envelope.
     * @param mixed $options Request data, a second path value, or legacy request options.
     * @param array<string, mixed> $requestOptions
     * @return mixed
     */
    public function trackVehicle($parameters = [], $options = [], $requestOptions = [])
    {
        return $this->endpointFromArguments('PATCH', '{{base_url}}/{{namespace}}/vehicles/:id/track', ['id'], 'body', func_get_args());
    }

    /**
     * Update a Vehicle.
     *
     * @param scalar|\Fleetbase\Sdk\Resource|array<string, mixed> $parameters First path value, or the legacy endpoint envelope.
     * @param mixed $options Request data, a second path value, or legacy request options.
     * @param array<string, mixed> $requestOptions
     * @return mixed
     */
    public function updateVehicle($parameters = [], $options = [], $requestOptions = [])
    {
        return $this->endpointFromArguments('PUT', '{{base_url}}/{{namespace}}/vehicles/:id', ['id'], 'body', func_get_args());
    }
}
