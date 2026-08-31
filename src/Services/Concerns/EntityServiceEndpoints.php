<?php

/**
 * Generated from the locked Fleetbase Postman contract.
 * Do not edit by hand; run tools/generate-endpoint-services.php.
 *
 * @license https://www.gnu.org/licenses/agpl-3.0.html AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace Fleetbase\Sdk\Services\Concerns;

trait EntityServiceEndpoints
{
    /**
     * Create an Entity.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function createEntity(array $parameters = [], array $options = [])
    {
        return $this->endpoint('POST', '{{base_url}}/{{namespace}}/entities', $parameters, $options);
    }

    /**
     * Delete a Entity.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function deleteEntity(array $parameters = [], array $options = [])
    {
        return $this->endpoint('DELETE', '{{base_url}}/{{namespace}}/entities/:id', $parameters, $options);
    }

    /**
     * Query Entities.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function queryEntities(array $parameters = [], array $options = [])
    {
        return $this->endpoint('GET', '{{base_url}}/{{namespace}}/entities', $parameters, $options);
    }

    /**
     * Retrieve an Entity.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function retrieveEntity(array $parameters = [], array $options = [])
    {
        return $this->endpoint('GET', '{{base_url}}/{{namespace}}/entities/:id', $parameters, $options);
    }

    /**
     * Update a Entity.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function updateEntity(array $parameters = [], array $options = [])
    {
        return $this->endpoint('PUT', '{{base_url}}/{{namespace}}/entities/:id', $parameters, $options);
    }
}
