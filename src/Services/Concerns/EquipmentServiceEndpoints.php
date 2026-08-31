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
        return $this->endpoint('POST', '{{base_url}}/{{namespace}}/equipment', $parameters, $options);
    }

    /**
     * Delete Equipment.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function deleteEquipment(array $parameters = [], array $options = [])
    {
        return $this->endpoint('DELETE', '{{base_url}}/{{namespace}}/equipment/{{equipment_id}}', $parameters, $options);
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
        return $this->endpoint('GET', '{{base_url}}/{{namespace}}/equipment', $parameters, $options);
    }

    /**
     * Retrieve Equipment.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function retrieveEquipment(array $parameters = [], array $options = [])
    {
        return $this->endpoint('GET', '{{base_url}}/{{namespace}}/equipment/{{equipment_id}}', $parameters, $options);
    }

    /**
     * Update Equipment.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function updateEquipment(array $parameters = [], array $options = [])
    {
        return $this->endpoint('PUT', '{{base_url}}/{{namespace}}/equipment/{{equipment_id}}', $parameters, $options);
    }
}
