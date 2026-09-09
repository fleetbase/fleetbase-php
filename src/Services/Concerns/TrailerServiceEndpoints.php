<?php

/**
 * Generated from the locked Fleetbase Postman contract.
 * Do not edit by hand; run tools/generate-endpoint-services.php.
 *
 * @license https://www.gnu.org/licenses/agpl-3.0.html AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace Fleetbase\Sdk\Services\Concerns;

trait TrailerServiceEndpoints
{
    /**
     * Attach Trailer to Vehicle.
     *
     * @param scalar|\Fleetbase\Sdk\Resource|array<string, mixed> $parameters First path value, or the legacy endpoint envelope.
     * @param mixed $options Request data, a second path value, or legacy request options.
     * @param array<string, mixed> $requestOptions
     * @return mixed
     */
    public function attachTrailerToVehicle($parameters = [], $options = [], $requestOptions = [])
    {
        return $this->endpointFromArguments('POST', '{{base_url}}/{{namespace}}/trailers/:id/attach', ['id'], 'body', func_get_args());
    }

    /**
     * Create a Trailer.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function createTrailer(array $parameters = [], array $options = [])
    {
        return $this->endpointFromArguments('POST', '{{base_url}}/{{namespace}}/trailers', [], 'body', func_get_args());
    }

    /**
     * Delete a Trailer.
     *
     * @param scalar|\Fleetbase\Sdk\Resource|array<string, mixed> $parameters First path value, or the legacy endpoint envelope.
     * @param mixed $options Request data, a second path value, or legacy request options.
     * @param array<string, mixed> $requestOptions
     * @return mixed
     */
    public function deleteTrailer($parameters = [], $options = [], $requestOptions = [])
    {
        return $this->endpointFromArguments('DELETE', '{{base_url}}/{{namespace}}/trailers/:id', ['id'], 'body', func_get_args());
    }

    /**
     * Detach Trailer from Vehicle.
     *
     * @param scalar|\Fleetbase\Sdk\Resource|array<string, mixed> $parameters First path value, or the legacy endpoint envelope.
     * @param mixed $options Request data, a second path value, or legacy request options.
     * @param array<string, mixed> $requestOptions
     * @return mixed
     */
    public function detachTrailerFromVehicle($parameters = [], $options = [], $requestOptions = [])
    {
        return $this->endpointFromArguments('POST', '{{base_url}}/{{namespace}}/trailers/:id/detach', ['id'], 'body', func_get_args());
    }

    /**
     * List Trailer Connections.
     *
     * @param scalar|\Fleetbase\Sdk\Resource|array<string, mixed> $parameters First path value, or the legacy endpoint envelope.
     * @param mixed $options Request data, a second path value, or legacy request options.
     * @param array<string, mixed> $requestOptions
     * @return mixed
     */
    public function listTrailerConnections($parameters = [], $options = [], $requestOptions = [])
    {
        return $this->endpointFromArguments('GET', '{{base_url}}/{{namespace}}/trailers/:id/connections', ['id'], 'query', func_get_args());
    }

    /**
     * Query Trailers.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function queryTrailers(array $parameters = [], array $options = [])
    {
        return $this->endpointFromArguments('GET', '{{base_url}}/{{namespace}}/trailers', [], 'query', func_get_args());
    }

    /**
     * Retrieve a Trailer.
     *
     * @param scalar|\Fleetbase\Sdk\Resource|array<string, mixed> $parameters First path value, or the legacy endpoint envelope.
     * @param mixed $options Request data, a second path value, or legacy request options.
     * @param array<string, mixed> $requestOptions
     * @return mixed
     */
    public function retrieveTrailer($parameters = [], $options = [], $requestOptions = [])
    {
        return $this->endpointFromArguments('GET', '{{base_url}}/{{namespace}}/trailers/:id', ['id'], 'query', func_get_args());
    }

    /**
     * Track Trailer.
     *
     * @param scalar|\Fleetbase\Sdk\Resource|array<string, mixed> $parameters First path value, or the legacy endpoint envelope.
     * @param mixed $options Request data, a second path value, or legacy request options.
     * @param array<string, mixed> $requestOptions
     * @return mixed
     */
    public function trackTrailer($parameters = [], $options = [], $requestOptions = [])
    {
        return $this->endpointFromArguments('PATCH', '{{base_url}}/{{namespace}}/trailers/:id/track', ['id'], 'body', func_get_args());
    }

    /**
     * Update a Trailer.
     *
     * @param scalar|\Fleetbase\Sdk\Resource|array<string, mixed> $parameters First path value, or the legacy endpoint envelope.
     * @param mixed $options Request data, a second path value, or legacy request options.
     * @param array<string, mixed> $requestOptions
     * @return mixed
     */
    public function updateTrailer($parameters = [], $options = [], $requestOptions = [])
    {
        return $this->endpointFromArguments('PUT', '{{base_url}}/{{namespace}}/trailers/:id', ['id'], 'body', func_get_args());
    }
}
