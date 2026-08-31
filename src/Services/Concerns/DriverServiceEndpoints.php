<?php

/**
 * Generated from the locked Fleetbase Postman contract.
 * Do not edit by hand; run tools/generate-endpoint-services.php.
 *
 * @license https://www.gnu.org/licenses/agpl-3.0.html AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace Fleetbase\Sdk\Services\Concerns;

trait DriverServiceEndpoints
{
    /**
     * Change Driver Password.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function changeDriverPassword(array $parameters = [], array $options = [])
    {
        return $this->endpoint('POST', '{{base_url}}/{{namespace}}/drivers/:id/change-password', $parameters, $options);
    }

    /**
     * Create a Driver.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function createDriver(array $parameters = [], array $options = [])
    {
        return $this->endpoint('POST', '{{base_url}}/{{namespace}}/drivers', $parameters, $options);
    }

    /**
     * Delete a Driver.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function deleteDriver(array $parameters = [], array $options = [])
    {
        return $this->endpoint('DELETE', '{{base_url}}/{{namespace}}/drivers/:id', $parameters, $options);
    }

    /**
     * Get Driver Current Organization.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function getDriverCurrentOrganization(array $parameters = [], array $options = [])
    {
        return $this->endpoint('GET', '{{base_url}}/{{namespace}}/drivers/:id/current-organization', $parameters, $options);
    }

    /**
     * List Driver Manifests.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function listDriverManifests(array $parameters = [], array $options = [])
    {
        return $this->endpoint('GET', '{{base_url}}/{{namespace}}/drivers/:id/manifests', $parameters, $options);
    }

    /**
     * List Driver Organizations.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function listDriverOrganizations(array $parameters = [], array $options = [])
    {
        return $this->endpoint('GET', '{{base_url}}/{{namespace}}/drivers/:id/organizations', $parameters, $options);
    }

    /**
     * Login Driver.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function loginDriver(array $parameters = [], array $options = [])
    {
        return $this->endpoint('POST', '{{base_url}}/{{namespace}}/drivers/login', $parameters, $options);
    }

    /**
     * Query Drivers.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function queryDrivers(array $parameters = [], array $options = [])
    {
        return $this->endpoint('GET', '{{base_url}}/{{namespace}}/drivers', $parameters, $options);
    }

    /**
     * Register Device.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function registerDevice(array $parameters = [], array $options = [])
    {
        return $this->endpoint('POST', '{{base_url}}/{{namespace}}/drivers/register-device', $parameters, $options);
    }

    /**
     * Register Driver Device.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function registerDriverDevice(array $parameters = [], array $options = [])
    {
        return $this->endpoint('POST', '{{base_url}}/{{namespace}}/drivers/:id/register-device', $parameters, $options);
    }

    /**
     * Request Driver Login SMS.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function requestDriverLoginSms(array $parameters = [], array $options = [])
    {
        return $this->endpoint('POST', '{{base_url}}/{{namespace}}/drivers/login-with-sms', $parameters, $options);
    }

    /**
     * Request Driver Password Reset.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function requestDriverPasswordReset(array $parameters = [], array $options = [])
    {
        return $this->endpoint('POST', '{{base_url}}/{{namespace}}/drivers/forgot-password', $parameters, $options);
    }

    /**
     * Reset Driver Password.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function resetDriverPassword(array $parameters = [], array $options = [])
    {
        return $this->endpoint('POST', '{{base_url}}/{{namespace}}/drivers/reset-password', $parameters, $options);
    }

    /**
     * Retrieve a Driver.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function retrieveDriver(array $parameters = [], array $options = [])
    {
        return $this->endpoint('GET', '{{base_url}}/{{namespace}}/drivers/:id', $parameters, $options);
    }

    /**
     * Simulate Driver Route.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function simulateDriverRoute(array $parameters = [], array $options = [])
    {
        return $this->endpoint('POST', '{{base_url}}/{{namespace}}/drivers/:id/simulate', $parameters, $options);
    }

    /**
     * Switch Driver Organization.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function switchDriverOrganization(array $parameters = [], array $options = [])
    {
        return $this->endpoint('POST', '{{base_url}}/{{namespace}}/drivers/:id/switch-organization', $parameters, $options);
    }

    /**
     * Toggle Driver Online.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function toggleDriverOnline(array $parameters = [], array $options = [])
    {
        return $this->endpoint('POST', '{{base_url}}/{{namespace}}/drivers/:id/toggle-online', $parameters, $options);
    }

    /**
     * Track Driver.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function trackDriver(array $parameters = [], array $options = [])
    {
        return $this->endpoint('PATCH', '{{base_url}}/{{namespace}}/drivers/:id/track', $parameters, $options);
    }

    /**
     * Update a Driver.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function updateDriver(array $parameters = [], array $options = [])
    {
        return $this->endpoint('PUT', '{{base_url}}/{{namespace}}/drivers/:id', $parameters, $options);
    }

    /**
     * Verify Driver Login Code.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function verifyDriverLoginCode(array $parameters = [], array $options = [])
    {
        return $this->endpoint('POST', '{{base_url}}/{{namespace}}/drivers/verify-code', $parameters, $options);
    }
}
