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
        return $this->endpoint('POST', '{{base_url}}/{{namespace}}/fuel-transactions', $parameters, $options);
    }

    /**
     * Delete a Fuel Transaction.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function deleteFuelTransaction(array $parameters = [], array $options = [])
    {
        return $this->endpoint('DELETE', '{{base_url}}/{{namespace}}/fuel-transactions/{{fuel_transaction_id}}', $parameters, $options);
    }

    /**
     * Match Fuel Transaction Order.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function matchFuelTransactionOrder(array $parameters = [], array $options = [])
    {
        return $this->endpoint('POST', '{{base_url}}/{{namespace}}/fuel-transactions/{{fuel_transaction_id}}/match-order', $parameters, $options);
    }

    /**
     * Match Fuel Transaction Vehicle.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function matchFuelTransactionVehicle(array $parameters = [], array $options = [])
    {
        return $this->endpoint('POST', '{{base_url}}/{{namespace}}/fuel-transactions/{{fuel_transaction_id}}/match-vehicle', $parameters, $options);
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
        return $this->endpoint('GET', '{{base_url}}/{{namespace}}/fuel-transactions', $parameters, $options);
    }

    /**
     * Reprocess Fuel Transaction.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function reprocessFuelTransaction(array $parameters = [], array $options = [])
    {
        return $this->endpoint('POST', '{{base_url}}/{{namespace}}/fuel-transactions/{{fuel_transaction_id}}/reprocess', $parameters, $options);
    }

    /**
     * Retrieve a Fuel Transaction.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function retrieveFuelTransaction(array $parameters = [], array $options = [])
    {
        return $this->endpoint('GET', '{{base_url}}/{{namespace}}/fuel-transactions/{{fuel_transaction_id}}', $parameters, $options);
    }

    /**
     * Review Fuel Transaction.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function reviewFuelTransaction(array $parameters = [], array $options = [])
    {
        return $this->endpoint('POST', '{{base_url}}/{{namespace}}/fuel-transactions/{{fuel_transaction_id}}/review', $parameters, $options);
    }

    /**
     * Update a Fuel Transaction.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function updateFuelTransaction(array $parameters = [], array $options = [])
    {
        return $this->endpoint('PUT', '{{base_url}}/{{namespace}}/fuel-transactions/{{fuel_transaction_id}}', $parameters, $options);
    }
}
