<?php

/**
 * Generated from the locked Fleetbase Postman contract.
 * Do not edit by hand; run tools/generate-endpoint-services.php.
 *
 * @license https://www.gnu.org/licenses/agpl-3.0.html AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace Fleetbase\Sdk\Services\Concerns;

trait FuelTransactionServiceEndpoints
{
    /**
     * Create a Fuel Transaction.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function createFuelTransaction(array $parameters = [], array $options = [])
    {
        return $this->endpointFromArguments('POST', '{{base_url}}/{{namespace}}/fuel-transactions', [], 'body', func_get_args());
    }

    /**
     * Delete a Fuel Transaction.
     *
     * @param scalar|\Fleetbase\Sdk\Resource|array<string, mixed> $parameters First path value, or the legacy endpoint envelope.
     * @param mixed $options Request data, a second path value, or legacy request options.
     * @param array<string, mixed> $requestOptions
     * @return mixed
     */
    public function deleteFuelTransaction($parameters = [], $options = [], $requestOptions = [])
    {
        return $this->endpointFromArguments('DELETE', '{{base_url}}/{{namespace}}/fuel-transactions/{{fuel_transaction_id}}', ['fuel_transaction_id'], 'body', func_get_args());
    }

    /**
     * Match Fuel Transaction Order.
     *
     * @param scalar|\Fleetbase\Sdk\Resource|array<string, mixed> $parameters First path value, or the legacy endpoint envelope.
     * @param mixed $options Request data, a second path value, or legacy request options.
     * @param array<string, mixed> $requestOptions
     * @return mixed
     */
    public function matchFuelTransactionOrder($parameters = [], $options = [], $requestOptions = [])
    {
        return $this->endpointFromArguments('POST', '{{base_url}}/{{namespace}}/fuel-transactions/{{fuel_transaction_id}}/match-order', ['fuel_transaction_id'], 'body', func_get_args());
    }

    /**
     * Match Fuel Transaction Vehicle.
     *
     * @param scalar|\Fleetbase\Sdk\Resource|array<string, mixed> $parameters First path value, or the legacy endpoint envelope.
     * @param mixed $options Request data, a second path value, or legacy request options.
     * @param array<string, mixed> $requestOptions
     * @return mixed
     */
    public function matchFuelTransactionVehicle($parameters = [], $options = [], $requestOptions = [])
    {
        return $this->endpointFromArguments('POST', '{{base_url}}/{{namespace}}/fuel-transactions/{{fuel_transaction_id}}/match-vehicle', ['fuel_transaction_id'], 'body', func_get_args());
    }

    /**
     * Query Fuel Transactions.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function queryFuelTransactions(array $parameters = [], array $options = [])
    {
        return $this->endpointFromArguments('GET', '{{base_url}}/{{namespace}}/fuel-transactions', [], 'query', func_get_args());
    }

    /**
     * Reprocess Fuel Transaction.
     *
     * @param scalar|\Fleetbase\Sdk\Resource|array<string, mixed> $parameters First path value, or the legacy endpoint envelope.
     * @param mixed $options Request data, a second path value, or legacy request options.
     * @param array<string, mixed> $requestOptions
     * @return mixed
     */
    public function reprocessFuelTransaction($parameters = [], $options = [], $requestOptions = [])
    {
        return $this->endpointFromArguments('POST', '{{base_url}}/{{namespace}}/fuel-transactions/{{fuel_transaction_id}}/reprocess', ['fuel_transaction_id'], 'body', func_get_args());
    }

    /**
     * Retrieve a Fuel Transaction.
     *
     * @param scalar|\Fleetbase\Sdk\Resource|array<string, mixed> $parameters First path value, or the legacy endpoint envelope.
     * @param mixed $options Request data, a second path value, or legacy request options.
     * @param array<string, mixed> $requestOptions
     * @return mixed
     */
    public function retrieveFuelTransaction($parameters = [], $options = [], $requestOptions = [])
    {
        return $this->endpointFromArguments('GET', '{{base_url}}/{{namespace}}/fuel-transactions/{{fuel_transaction_id}}', ['fuel_transaction_id'], 'query', func_get_args());
    }

    /**
     * Review Fuel Transaction.
     *
     * @param scalar|\Fleetbase\Sdk\Resource|array<string, mixed> $parameters First path value, or the legacy endpoint envelope.
     * @param mixed $options Request data, a second path value, or legacy request options.
     * @param array<string, mixed> $requestOptions
     * @return mixed
     */
    public function reviewFuelTransaction($parameters = [], $options = [], $requestOptions = [])
    {
        return $this->endpointFromArguments('POST', '{{base_url}}/{{namespace}}/fuel-transactions/{{fuel_transaction_id}}/review', ['fuel_transaction_id'], 'body', func_get_args());
    }

    /**
     * Update a Fuel Transaction.
     *
     * @param scalar|\Fleetbase\Sdk\Resource|array<string, mixed> $parameters First path value, or the legacy endpoint envelope.
     * @param mixed $options Request data, a second path value, or legacy request options.
     * @param array<string, mixed> $requestOptions
     * @return mixed
     */
    public function updateFuelTransaction($parameters = [], $options = [], $requestOptions = [])
    {
        return $this->endpointFromArguments('PUT', '{{base_url}}/{{namespace}}/fuel-transactions/{{fuel_transaction_id}}', ['fuel_transaction_id'], 'body', func_get_args());
    }
}
