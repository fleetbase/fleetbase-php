<?php

/**
 * Generated from the locked Fleetbase Postman contract.
 * Do not edit by hand; run tools/generate-endpoint-services.php.
 *
 * @license https://www.gnu.org/licenses/agpl-3.0.html AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace Fleetbase\Sdk\Services\Concerns;

trait TrackingNumberServiceEndpoints
{
    /**
     * Create a Tracking Number.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function createTrackingNumber(array $parameters = [], array $options = [])
    {
        return $this->endpoint('POST', '{{base_url}}/{{namespace}}/tracking-numbers', $parameters, $options);
    }

    /**
     * Decode Tracking Number QR.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function decodeTrackingNumberQr(array $parameters = [], array $options = [])
    {
        return $this->endpoint('POST', '{{base_url}}/{{namespace}}/tracking-numbers/from-qr', $parameters, $options);
    }

    /**
     * Delete a Tracking Number.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function deleteTrackingNumber(array $parameters = [], array $options = [])
    {
        return $this->endpoint('DELETE', '{{base_url}}/{{namespace}}/tracking-numbers/:id', $parameters, $options);
    }

    /**
     * Query Tracking Numbers.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function queryTrackingNumbers(array $parameters = [], array $options = [])
    {
        return $this->endpoint('GET', '{{base_url}}/{{namespace}}/tracking-numbers', $parameters, $options);
    }

    /**
     * Retrieve a Tracking Number.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function retrieveTrackingNumber(array $parameters = [], array $options = [])
    {
        return $this->endpoint('GET', '{{base_url}}/{{namespace}}/tracking-numbers/:id', $parameters, $options);
    }
}
