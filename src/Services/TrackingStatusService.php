<?php

/**
 * Generated from the locked Fleetbase Postman contract.
 * Do not edit by hand; run tools/generate-endpoint-services.php.
 *
 * @license https://www.gnu.org/licenses/agpl-3.0.html AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace Fleetbase\Sdk\Services;

use Fleetbase\Sdk\EndpointService;
use Fleetbase\Sdk\HttpClient;
use Fleetbase\Sdk\Services\Concerns\TrackingStatusServiceEndpoints;

class TrackingStatusService extends EndpointService
{
    use TrackingStatusServiceEndpoints;

    /** @param array<string, mixed> $options */
    public function __construct(HttpClient $client, array $options = [])
    {
        parent::__construct('TrackingStatus', $client, array_merge(['namespace' => 'tracking-statuses'], $options));
    }
}
