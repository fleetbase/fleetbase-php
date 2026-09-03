<?php

/**
 * Generated from the locked Fleetbase Postman contract.
 * Do not edit by hand; run tools/generate-endpoint-services.php.
 *
 * @license https://www.gnu.org/licenses/agpl-3.0.html AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace Fleetbase\Sdk\Services\Concerns;

trait ServiceAreaServiceEndpoints
{
    /**
     * Create a Service Area.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function createServiceArea(array $parameters = [], array $options = [])
    {
        return $this->endpointFromArguments('POST', '{{base_url}}/{{namespace}}/service-areas', [], 'body', func_get_args());
    }

    /**
     * Delete a Service Area.
     *
     * @param scalar|\Fleetbase\Sdk\Resource|array<string, mixed> $parameters First path value, or the legacy endpoint envelope.
     * @param mixed $options Request data, a second path value, or legacy request options.
     * @param array<string, mixed> $requestOptions
     * @return mixed
     */
    public function deleteServiceArea($parameters = [], $options = [], $requestOptions = [])
    {
        return $this->endpointFromArguments('DELETE', '{{base_url}}/{{namespace}}/service-areas/:id', ['id'], 'body', func_get_args());
    }

    /**
     * Query Service Areas.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function queryServiceAreas(array $parameters = [], array $options = [])
    {
        return $this->endpointFromArguments('GET', '{{base_url}}/{{namespace}}/service-areas', [], 'query', func_get_args());
    }

    /**
     * Retrieve a Service Area.
     *
     * @param scalar|\Fleetbase\Sdk\Resource|array<string, mixed> $parameters First path value, or the legacy endpoint envelope.
     * @param mixed $options Request data, a second path value, or legacy request options.
     * @param array<string, mixed> $requestOptions
     * @return mixed
     */
    public function retrieveServiceArea($parameters = [], $options = [], $requestOptions = [])
    {
        return $this->endpointFromArguments('GET', '{{base_url}}/{{namespace}}/service-areas/:id', ['id'], 'query', func_get_args());
    }

    /**
     * Update a Service Area.
     *
     * @param scalar|\Fleetbase\Sdk\Resource|array<string, mixed> $parameters First path value, or the legacy endpoint envelope.
     * @param mixed $options Request data, a second path value, or legacy request options.
     * @param array<string, mixed> $requestOptions
     * @return mixed
     */
    public function updateServiceArea($parameters = [], $options = [], $requestOptions = [])
    {
        return $this->endpointFromArguments('PUT', '{{base_url}}/{{namespace}}/service-areas/:id', ['id'], 'body', func_get_args());
    }
}
