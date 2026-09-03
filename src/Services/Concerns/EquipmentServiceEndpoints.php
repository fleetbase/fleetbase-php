<?php

/**
 * Generated from the locked Fleetbase Postman contract.
 * Do not edit by hand; run tools/generate-endpoint-services.php.
 *
 * @license https://www.gnu.org/licenses/agpl-3.0.html AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace Fleetbase\Sdk\Services\Concerns;

trait EquipmentServiceEndpoints
{
    /**
     * Create Equipment.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function createEquipment(array $parameters = [], array $options = [])
    {
        return $this->endpointFromArguments('POST', '{{base_url}}/{{namespace}}/equipment', [], 'body', func_get_args());
    }

    /**
     * Delete Equipment.
     *
     * @param scalar|\Fleetbase\Sdk\Resource|array<string, mixed> $parameters First path value, or the legacy endpoint envelope.
     * @param mixed $options Request data, a second path value, or legacy request options.
     * @param array<string, mixed> $requestOptions
     * @return mixed
     */
    public function deleteEquipment($parameters = [], $options = [], $requestOptions = [])
    {
        return $this->endpointFromArguments('DELETE', '{{base_url}}/{{namespace}}/equipment/{{equipment_id}}', ['equipment_id'], 'body', func_get_args());
    }

    /**
     * Query Equipment.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function queryEquipment(array $parameters = [], array $options = [])
    {
        return $this->endpointFromArguments('GET', '{{base_url}}/{{namespace}}/equipment', [], 'query', func_get_args());
    }

    /**
     * Retrieve Equipment.
     *
     * @param scalar|\Fleetbase\Sdk\Resource|array<string, mixed> $parameters First path value, or the legacy endpoint envelope.
     * @param mixed $options Request data, a second path value, or legacy request options.
     * @param array<string, mixed> $requestOptions
     * @return mixed
     */
    public function retrieveEquipment($parameters = [], $options = [], $requestOptions = [])
    {
        return $this->endpointFromArguments('GET', '{{base_url}}/{{namespace}}/equipment/{{equipment_id}}', ['equipment_id'], 'query', func_get_args());
    }

    /**
     * Update Equipment.
     *
     * @param scalar|\Fleetbase\Sdk\Resource|array<string, mixed> $parameters First path value, or the legacy endpoint envelope.
     * @param mixed $options Request data, a second path value, or legacy request options.
     * @param array<string, mixed> $requestOptions
     * @return mixed
     */
    public function updateEquipment($parameters = [], $options = [], $requestOptions = [])
    {
        return $this->endpointFromArguments('PUT', '{{base_url}}/{{namespace}}/equipment/{{equipment_id}}', ['equipment_id'], 'body', func_get_args());
    }
}
