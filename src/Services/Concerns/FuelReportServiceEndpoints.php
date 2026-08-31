<?php

/**
 * Generated from the locked Fleetbase Postman contract.
 * Do not edit by hand; run tools/generate-endpoint-services.php.
 *
 * @license https://www.gnu.org/licenses/agpl-3.0.html AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace Fleetbase\Sdk\Services\Concerns;

trait FuelReportServiceEndpoints
{
    /**
     * Create a Fuel Report.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function createFuelReport(array $parameters = [], array $options = [])
    {
        return $this->endpoint('POST', '{{base_url}}/{{namespace}}/fuel-reports', $parameters, $options);
    }

    /**
     * Delete a Fuel Report.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function deleteFuelReport(array $parameters = [], array $options = [])
    {
        return $this->endpoint('DELETE', '{{base_url}}/{{namespace}}/fuel-reports/:id', $parameters, $options);
    }

    /**
     * Query Fuel Reports.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function queryFuelReports(array $parameters = [], array $options = [])
    {
        return $this->endpoint('GET', '{{base_url}}/{{namespace}}/fuel-reports', $parameters, $options);
    }

    /**
     * Retrieve a Fuel Report.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function retrieveFuelReport(array $parameters = [], array $options = [])
    {
        return $this->endpoint('GET', '{{base_url}}/{{namespace}}/fuel-reports/:id', $parameters, $options);
    }

    /**
     * Update a Fuel Report.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function updateFuelReport(array $parameters = [], array $options = [])
    {
        return $this->endpoint('PUT', '{{base_url}}/{{namespace}}/fuel-reports/:id', $parameters, $options);
    }
}
