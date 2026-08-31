<?php

/**
 * Generated from the locked Fleetbase Postman contract.
 * Do not edit by hand; run tools/generate-endpoint-services.php.
 *
 * @license https://www.gnu.org/licenses/agpl-3.0.html AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace Fleetbase\Sdk\Services\Concerns;

trait GeofenceServiceEndpoints
{
    /**
     * Get Driver Geofence History.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function getDriverGeofenceHistory(array $parameters = [], array $options = [])
    {
        return $this->endpoint('GET', '{{base_url}}/{{namespace}}/geofences/driver/:driverId/history', $parameters, $options);
    }

    /**
     * Get Geofence Dwell Report.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function getGeofenceDwellReport(array $parameters = [], array $options = [])
    {
        return $this->endpoint('GET', '{{base_url}}/{{namespace}}/geofences/dwell-report', $parameters, $options);
    }

    /**
     * Get Geofence Inventory.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function getGeofenceInventory(array $parameters = [], array $options = [])
    {
        return $this->endpoint('GET', '{{base_url}}/{{namespace}}/geofences/inventory', $parameters, $options);
    }

    /**
     * List Geofence Events.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function listGeofenceEvents(array $parameters = [], array $options = [])
    {
        return $this->endpoint('GET', '{{base_url}}/{{namespace}}/geofences/events', $parameters, $options);
    }
}
