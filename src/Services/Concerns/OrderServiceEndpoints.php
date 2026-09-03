<?php

/**
 * Generated from the locked Fleetbase Postman contract.
 * Do not edit by hand; run tools/generate-endpoint-services.php.
 *
 * @license https://www.gnu.org/licenses/agpl-3.0.html AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace Fleetbase\Sdk\Services\Concerns;

trait OrderServiceEndpoints
{
    /**
     * Cancel an Order.
     *
     * @param scalar|\Fleetbase\Sdk\Resource|array<string, mixed> $parameters First path value, or the legacy endpoint envelope.
     * @param mixed $options Request data, a second path value, or legacy request options.
     * @param array<string, mixed> $requestOptions
     * @return mixed
     */
    public function cancelOrder($parameters = [], $options = [], $requestOptions = [])
    {
        return $this->endpointFromArguments('DELETE', '{{base_url}}/{{namespace}}/orders/:id/cancel', ['id'], 'body', func_get_args());
    }

    /**
     * Capture Photo for Order.
     *
     * @param scalar|\Fleetbase\Sdk\Resource|array<string, mixed> $parameters First path value, or the legacy endpoint envelope.
     * @param mixed $options Request data, a second path value, or legacy request options.
     * @param array<string, mixed> $data
     * @param array<string, mixed> $requestOptions
     * @return mixed
     */
    public function capturePhotoForOrder($parameters = [], $options = [], $data = [], $requestOptions = [])
    {
        return $this->endpointFromArguments('POST', '{{base_url}}/{{namespace}}/orders/:id/capture-photo/:subjectId', ['id', 'subjectId'], 'body', func_get_args());
    }

    /**
     * Capture QR Code for Order.
     *
     * @param scalar|\Fleetbase\Sdk\Resource|array<string, mixed> $parameters First path value, or the legacy endpoint envelope.
     * @param mixed $options Request data, a second path value, or legacy request options.
     * @param array<string, mixed> $data
     * @param array<string, mixed> $requestOptions
     * @return mixed
     */
    public function captureQrCodeForOrder($parameters = [], $options = [], $data = [], $requestOptions = [])
    {
        return $this->endpointFromArguments('POST', '{{base_url}}/{{namespace}}/orders/:id/capture-qr/:subject-id', ['id', 'subject-id'], 'body', func_get_args());
    }

    /**
     * Capture Signature for Order.
     *
     * @param scalar|\Fleetbase\Sdk\Resource|array<string, mixed> $parameters First path value, or the legacy endpoint envelope.
     * @param mixed $options Request data, a second path value, or legacy request options.
     * @param array<string, mixed> $data
     * @param array<string, mixed> $requestOptions
     * @return mixed
     */
    public function captureSignatureForOrder($parameters = [], $options = [], $data = [], $requestOptions = [])
    {
        return $this->endpointFromArguments('POST', '{{base_url}}/{{namespace}}/orders/:id/capture-signature/:subject-id', ['id', 'subject-id'], 'body', func_get_args());
    }

    /**
     * Complete an Order.
     *
     * @param scalar|\Fleetbase\Sdk\Resource|array<string, mixed> $parameters First path value, or the legacy endpoint envelope.
     * @param mixed $options Request data, a second path value, or legacy request options.
     * @param array<string, mixed> $requestOptions
     * @return mixed
     */
    public function completeOrder($parameters = [], $options = [], $requestOptions = [])
    {
        return $this->endpointFromArguments('POST', '{{base_url}}/{{namespace}}/orders/:id/complete', ['id'], 'body', func_get_args());
    }

    /**
     * Create an Order.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function createOrder(array $parameters = [], array $options = [])
    {
        return $this->endpointFromArguments('POST', '{{base_url}}/{{namespace}}/orders', [], 'body', func_get_args());
    }

    /**
     * Create an Order using Complete Payload.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function createOrderUsingCompletePayload(array $parameters = [], array $options = [])
    {
        return $this->endpointFromArguments('POST', '{{base_url}}/{{namespace}}/orders', [], 'body', func_get_args());
    }

    /**
     * Create an Order using Coordinates.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function createOrderUsingCoordinates(array $parameters = [], array $options = [])
    {
        return $this->endpointFromArguments('POST', '{{base_url}}/{{namespace}}/orders', [], 'body', func_get_args());
    }

    /**
     * Create an Order using GeoJSON Points.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function createOrderUsingGeojsonPoints(array $parameters = [], array $options = [])
    {
        return $this->endpointFromArguments('POST', '{{base_url}}/{{namespace}}/orders', [], 'body', func_get_args());
    }

    /**
     * Create an Order using only Pickup Dropoff.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function createOrderUsingOnlyPickupDropoff(array $parameters = [], array $options = [])
    {
        return $this->endpointFromArguments('POST', '{{base_url}}/{{namespace}}/orders', [], 'body', func_get_args());
    }

    /**
     * Create an Order using only Waypoints.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function createOrderUsingOnlyWaypoints(array $parameters = [], array $options = [])
    {
        return $this->endpointFromArguments('POST', '{{base_url}}/{{namespace}}/orders', [], 'body', func_get_args());
    }

    /**
     * Create an Order using Payload.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function createOrderUsingPayload(array $parameters = [], array $options = [])
    {
        return $this->endpointFromArguments('POST', '{{base_url}}/{{namespace}}/orders', [], 'body', func_get_args());
    }

    /**
     * Create an Order using Waypoints and Entities with Photos.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function createOrderUsingWaypointsAndEntitiesWithPhotos(array $parameters = [], array $options = [])
    {
        return $this->endpointFromArguments('POST', '{{base_url}}/{{namespace}}/orders', [], 'body', func_get_args());
    }

    /**
     * Create an Order using Waypoints and Entity Destinations.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function createOrderUsingWaypointsAndEntityDestinations(array $parameters = [], array $options = [])
    {
        return $this->endpointFromArguments('POST', '{{base_url}}/{{namespace}}/orders', [], 'body', func_get_args());
    }

    /**
     * Delete an Order.
     *
     * @param scalar|\Fleetbase\Sdk\Resource|array<string, mixed> $parameters First path value, or the legacy endpoint envelope.
     * @param mixed $options Request data, a second path value, or legacy request options.
     * @param array<string, mixed> $requestOptions
     * @return mixed
     */
    public function deleteOrder($parameters = [], $options = [], $requestOptions = [])
    {
        return $this->endpointFromArguments('DELETE', '{{base_url}}/{{namespace}}/orders/:id', ['id'], 'body', func_get_args());
    }

    /**
     * Dispatch an Order.
     *
     * @param scalar|\Fleetbase\Sdk\Resource|array<string, mixed> $parameters First path value, or the legacy endpoint envelope.
     * @param mixed $options Request data, a second path value, or legacy request options.
     * @param array<string, mixed> $requestOptions
     * @return mixed
     */
    public function dispatchOrder($parameters = [], $options = [], $requestOptions = [])
    {
        return $this->endpointFromArguments('PATCH', '{{base_url}}/{{namespace}}/orders/:id/dispatch', ['id'], 'body', func_get_args());
    }

    /**
     * Get Editable Entity Fields.
     *
     * @param scalar|\Fleetbase\Sdk\Resource|array<string, mixed> $parameters First path value, or the legacy endpoint envelope.
     * @param mixed $options Request data, a second path value, or legacy request options.
     * @param array<string, mixed> $requestOptions
     * @return mixed
     */
    public function getEditableEntityFields($parameters = [], $options = [], $requestOptions = [])
    {
        return $this->endpointFromArguments('GET', '{{base_url}}/{{namespace}}/orders/:id/editable-entity-fields', ['id'], 'query', func_get_args());
    }

    /**
     * Get Order Distance and Time.
     *
     * @param scalar|\Fleetbase\Sdk\Resource|array<string, mixed> $parameters First path value, or the legacy endpoint envelope.
     * @param mixed $options Request data, a second path value, or legacy request options.
     * @param array<string, mixed> $requestOptions
     * @return mixed
     */
    public function getOrderDistanceAndTime($parameters = [], $options = [], $requestOptions = [])
    {
        return $this->endpointFromArguments('GET', '{{base_url}}/{{namespace}}/orders/:id/distance-and-time', ['id'], 'query', func_get_args());
    }

    /**
     * Get Order ETA.
     *
     * @param scalar|\Fleetbase\Sdk\Resource|array<string, mixed> $parameters First path value, or the legacy endpoint envelope.
     * @param mixed $options Request data, a second path value, or legacy request options.
     * @param array<string, mixed> $requestOptions
     * @return mixed
     */
    public function getOrderEta($parameters = [], $options = [], $requestOptions = [])
    {
        return $this->endpointFromArguments('GET', '{{base_url}}/{{namespace}}/orders/:id/eta', ['id'], 'query', func_get_args());
    }

    /**
     * Get Order Next Activity.
     *
     * @param scalar|\Fleetbase\Sdk\Resource|array<string, mixed> $parameters First path value, or the legacy endpoint envelope.
     * @param mixed $options Request data, a second path value, or legacy request options.
     * @param array<string, mixed> $requestOptions
     * @return mixed
     */
    public function getOrderNextActivity($parameters = [], $options = [], $requestOptions = [])
    {
        return $this->endpointFromArguments('GET', '{{base_url}}/{{namespace}}/orders/:id/next-activity', ['id'], 'query', func_get_args());
    }

    /**
     * Get Order Tracker.
     *
     * @param scalar|\Fleetbase\Sdk\Resource|array<string, mixed> $parameters First path value, or the legacy endpoint envelope.
     * @param mixed $options Request data, a second path value, or legacy request options.
     * @param array<string, mixed> $requestOptions
     * @return mixed
     */
    public function getOrderTracker($parameters = [], $options = [], $requestOptions = [])
    {
        return $this->endpointFromArguments('GET', '{{base_url}}/{{namespace}}/orders/:id/tracker', ['id'], 'query', func_get_args());
    }

    /**
     * List Order Comments.
     *
     * @param scalar|\Fleetbase\Sdk\Resource|array<string, mixed> $parameters First path value, or the legacy endpoint envelope.
     * @param mixed $options Request data, a second path value, or legacy request options.
     * @param array<string, mixed> $requestOptions
     * @return mixed
     */
    public function listOrderComments($parameters = [], $options = [], $requestOptions = [])
    {
        return $this->endpointFromArguments('GET', '{{base_url}}/{{namespace}}/orders/:id/comments', ['id'], 'query', func_get_args());
    }

    /**
     * List Order Proofs.
     *
     * @param scalar|\Fleetbase\Sdk\Resource|array<string, mixed> $parameters First path value, or the legacy endpoint envelope.
     * @param mixed $options Request data, a second path value, or legacy request options.
     * @param array<string, mixed> $data
     * @param array<string, mixed> $requestOptions
     * @return mixed
     */
    public function listOrderProofs($parameters = [], $options = [], $data = [], $requestOptions = [])
    {
        return $this->endpointFromArguments('GET', '{{base_url}}/{{namespace}}/orders/:id/proofs/:subjectId', ['id', 'subjectId'], 'query', func_get_args());
    }

    /**
     * Query Orders.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function queryOrders(array $parameters = [], array $options = [])
    {
        return $this->endpointFromArguments('GET', '{{base_url}}/{{namespace}}/orders', [], 'query', func_get_args());
    }

    /**
     * Retrieve an Order.
     *
     * @param scalar|\Fleetbase\Sdk\Resource|array<string, mixed> $parameters First path value, or the legacy endpoint envelope.
     * @param mixed $options Request data, a second path value, or legacy request options.
     * @param array<string, mixed> $requestOptions
     * @return mixed
     */
    public function retrieveOrder($parameters = [], $options = [], $requestOptions = [])
    {
        return $this->endpointFromArguments('GET', '{{base_url}}/{{namespace}}/orders/{{order_id}}', ['order_id'], 'query', func_get_args());
    }

    /**
     * Schedule an Order.
     *
     * @param scalar|\Fleetbase\Sdk\Resource|array<string, mixed> $parameters First path value, or the legacy endpoint envelope.
     * @param mixed $options Request data, a second path value, or legacy request options.
     * @param array<string, mixed> $requestOptions
     * @return mixed
     */
    public function scheduleOrder($parameters = [], $options = [], $requestOptions = [])
    {
        return $this->endpointFromArguments('PATCH', '{{base_url}}/{{namespace}}/orders/:id/schedule', ['id'], 'body', func_get_args());
    }

    /**
     * Set Order Destination.
     *
     * @param scalar|\Fleetbase\Sdk\Resource|array<string, mixed> $parameters First path value, or the legacy endpoint envelope.
     * @param mixed $options Request data, a second path value, or legacy request options.
     * @param array<string, mixed> $data
     * @param array<string, mixed> $requestOptions
     * @return mixed
     */
    public function setOrderDestination($parameters = [], $options = [], $data = [], $requestOptions = [])
    {
        return $this->endpointFromArguments('PATCH', '{{base_url}}/{{namespace}}/orders/:id/set-destination/:placeId', ['id', 'placeId'], 'body', func_get_args());
    }

    /**
     * Start an Order.
     *
     * @param scalar|\Fleetbase\Sdk\Resource|array<string, mixed> $parameters First path value, or the legacy endpoint envelope.
     * @param mixed $options Request data, a second path value, or legacy request options.
     * @param array<string, mixed> $requestOptions
     * @return mixed
     */
    public function startOrder($parameters = [], $options = [], $requestOptions = [])
    {
        return $this->endpointFromArguments('POST', '{{base_url}}/{{namespace}}/orders/:id/start', ['id'], 'body', func_get_args());
    }

    /**
     * Update an Order.
     *
     * @param scalar|\Fleetbase\Sdk\Resource|array<string, mixed> $parameters First path value, or the legacy endpoint envelope.
     * @param mixed $options Request data, a second path value, or legacy request options.
     * @param array<string, mixed> $requestOptions
     * @return mixed
     */
    public function updateOrder($parameters = [], $options = [], $requestOptions = [])
    {
        return $this->endpointFromArguments('PUT', '{{base_url}}/{{namespace}}/orders/:id', ['id'], 'body', func_get_args());
    }

    /**
     * Update Order Activity.
     *
     * @param scalar|\Fleetbase\Sdk\Resource|array<string, mixed> $parameters First path value, or the legacy endpoint envelope.
     * @param mixed $options Request data, a second path value, or legacy request options.
     * @param array<string, mixed> $requestOptions
     * @return mixed
     */
    public function updateOrderActivity($parameters = [], $options = [], $requestOptions = [])
    {
        return $this->endpointFromArguments('POST', '{{base_url}}/{{namespace}}/orders/:id/update-activity', ['id'], 'body', func_get_args());
    }
}
