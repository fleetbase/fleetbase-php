<?php

/**
 * This file is part of the fleetbase/fleetbase-php library.
 *
 * @copyright Copyright (c) Fleetbase Pte Ltd. <ron@fleetbase.io>
 * @license https://www.gnu.org/licenses/agpl-3.0.html AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace Fleetbase\Sdk\Services;

use Fleetbase\Sdk\HttpClient;
use Fleetbase\Sdk\Service;

/**
 * Realtime (SocketCluster) helpers.
 *
 * Hand-written: this service is not generated from the Postman contract, so
 * tools/generate-endpoint-services.php leaves it untouched.
 */
class SocketService extends Service
{
    /** @param array<string, mixed> $options */
    public function __construct(HttpClient $client, array $options = [])
    {
        parent::__construct('Socket', $client, array_merge(['namespace' => 'socket'], $options));
    }

    /**
     * Mint a short-lived realtime socket token: `POST socket/token`.
     *
     * Call this on your server with your secret API key and hand only the
     * returned token to the browser or device, which presents it with
     * `socket.authenticate(token)`. An API-key token may subscribe to the
     * key's company channel (`company.{company uuid}`), its own key channel
     * (`api.{key id}`), and channels of resources in the same company.
     * Refresh it about 60 seconds before `expires_in` elapses.
     *
     * The decoded response has `token` (string), `expires_in` (seconds) and
     * `expires_at` (ISO 8601). A server without realtime authentication
     * configured responds 404, raised as a NotFoundException.
     *
     * @param array<string, mixed> $options Request options.
     * @return mixed
     */
    public function token(array $options = [])
    {
        return $this->client->post($this->uri('token'), [], $options);
    }
}
