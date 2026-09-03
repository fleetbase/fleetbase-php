<?php

/**
 * Generated from the locked Fleetbase Postman contract.
 * Do not edit by hand; run tools/generate-endpoint-services.php.
 *
 * @license https://www.gnu.org/licenses/agpl-3.0.html AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace Fleetbase\Sdk\Services\Concerns;

trait CustomerServiceEndpoints
{
    /**
     * Create a Customer.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function createCustomer(array $parameters = [], array $options = [])
    {
        return $this->endpointFromArguments('POST', '{{base_url}}/{{namespace}}/customers', [], 'body', func_get_args());
    }

    /**
     * Create a Customer Order.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function createCustomerOrder(array $parameters = [], array $options = [])
    {
        return $this->endpointFromArguments('POST', '{{base_url}}/{{namespace}}/customers/orders', [], 'body', func_get_args());
    }

    /**
     * Forgot Customer Password.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function forgotCustomerPassword(array $parameters = [], array $options = [])
    {
        return $this->endpointFromArguments('POST', '{{base_url}}/{{namespace}}/customers/forgot-password', [], 'body', func_get_args());
    }

    /**
     * List Customer Orders.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function listCustomerOrders(array $parameters = [], array $options = [])
    {
        return $this->endpointFromArguments('GET', '{{base_url}}/{{namespace}}/customers/orders', [], 'query', func_get_args());
    }

    /**
     * List Customer Places.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function listCustomerPlaces(array $parameters = [], array $options = [])
    {
        return $this->endpointFromArguments('GET', '{{base_url}}/{{namespace}}/customers/places', [], 'query', func_get_args());
    }

    /**
     * Login Customer.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function loginCustomer(array $parameters = [], array $options = [])
    {
        return $this->endpointFromArguments('POST', '{{base_url}}/{{namespace}}/customers/login', [], 'body', func_get_args());
    }

    /**
     * Logout All Customer Sessions.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function logoutAllCustomerSessions(array $parameters = [], array $options = [])
    {
        return $this->endpointFromArguments('POST', '{{base_url}}/{{namespace}}/customers/logout-all', [], 'body', func_get_args());
    }

    /**
     * Logout Customer.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function logoutCustomer(array $parameters = [], array $options = [])
    {
        return $this->endpointFromArguments('POST', '{{base_url}}/{{namespace}}/customers/logout', [], 'body', func_get_args());
    }

    /**
     * Register Customer Device.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function registerCustomerDevice(array $parameters = [], array $options = [])
    {
        return $this->endpointFromArguments('POST', '{{base_url}}/{{namespace}}/customers/register-device', [], 'body', func_get_args());
    }

    /**
     * Request Customer Creation Code.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function requestCustomerCreationCode(array $parameters = [], array $options = [])
    {
        return $this->endpointFromArguments('POST', '{{base_url}}/{{namespace}}/customers/request-creation-code', [], 'body', func_get_args());
    }

    /**
     * Request Customer Login SMS.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function requestCustomerLoginSms(array $parameters = [], array $options = [])
    {
        return $this->endpointFromArguments('POST', '{{base_url}}/{{namespace}}/customers/login-with-sms', [], 'body', func_get_args());
    }

    /**
     * Reset Customer Password.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function resetCustomerPassword(array $parameters = [], array $options = [])
    {
        return $this->endpointFromArguments('POST', '{{base_url}}/{{namespace}}/customers/reset-password', [], 'body', func_get_args());
    }

    /**
     * Retrieve Authenticated Customer.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function retrieveAuthenticatedCustomer(array $parameters = [], array $options = [])
    {
        return $this->endpointFromArguments('GET', '{{base_url}}/{{namespace}}/customers/me', [], 'query', func_get_args());
    }

    /**
     * Retrieve a Customer Order.
     *
     * @param scalar|\Fleetbase\Sdk\Resource|array<string, mixed> $parameters First path value, or the legacy endpoint envelope.
     * @param mixed $options Request data, a second path value, or legacy request options.
     * @param array<string, mixed> $requestOptions
     * @return mixed
     */
    public function retrieveCustomerOrder($parameters = [], $options = [], $requestOptions = [])
    {
        return $this->endpointFromArguments('GET', '{{base_url}}/{{namespace}}/customers/orders/{{customer_order_id}}', ['customer_order_id'], 'query', func_get_args());
    }

    /**
     * Update Authenticated Customer.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function updateAuthenticatedCustomer(array $parameters = [], array $options = [])
    {
        return $this->endpointFromArguments('PUT', '{{base_url}}/{{namespace}}/customers/me', [], 'body', func_get_args());
    }

    /**
     * Verify Customer Login Code.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function verifyCustomerLoginCode(array $parameters = [], array $options = [])
    {
        return $this->endpointFromArguments('POST', '{{base_url}}/{{namespace}}/customers/verify-code', [], 'body', func_get_args());
    }
}
