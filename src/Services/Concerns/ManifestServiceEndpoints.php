<?php

/**
 * Generated from the locked Fleetbase Postman contract.
 * Do not edit by hand; run tools/generate-endpoint-services.php.
 *
 * @license https://www.gnu.org/licenses/agpl-3.0.html AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace Fleetbase\Sdk\Services\Concerns;

trait ManifestServiceEndpoints
{
    /**
     * Optimize a Manifest.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function optimizeManifest(array $parameters = [], array $options = [])
    {
        return $this->endpoint('POST', '{{base_url}}/{{namespace}}/manifests/:id/optimize', $parameters, $options);
    }

    /**
     * Retrieve a Manifest.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function retrieveManifest(array $parameters = [], array $options = [])
    {
        return $this->endpoint('GET', '{{base_url}}/{{namespace}}/manifests/:id', $parameters, $options);
    }

    /**
     * Update a Manifest Stop.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function updateManifestStop(array $parameters = [], array $options = [])
    {
        return $this->endpoint('PATCH', '{{base_url}}/{{namespace}}/manifest-stops/:id', $parameters, $options);
    }
}
