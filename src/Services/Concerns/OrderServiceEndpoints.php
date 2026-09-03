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
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function cancelOrder(array $parameters = [], array $options = [])
    {
        return $this->endpoint('DELETE', '{{base_url}}/{{namespace}}/orders/:id/cancel', $parameters, $options);
    }

    /**
     * Capture Photo for Order.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function capturePhotoForOrder(array $parameters = [], array $options = [])
    {
        return $this->endpoint('POST', '{{base_url}}/{{namespace}}/orders/:id/capture-photo/:subjectId', $parameters, $options);
    }

    /**
     * Capture QR Code for Order.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function captureQrCodeForOrder(array $parameters = [], array $options = [])
    {
        return $this->endpoint('POST', '{{base_url}}/{{namespace}}/orders/:id/capture-qr/:subject-id', $parameters, $options);
    }

    /**
     * Capture Signature for Order.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function captureSignatureForOrder(array $parameters = [], array $options = [])
    {
        return $this->endpoint('POST', '{{base_url}}/{{namespace}}/orders/:id/capture-signature/:subject-id', $parameters, $options);
    }

    /**
     * Complete an Order.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function completeOrder(array $parameters = [], array $options = [])
    {
        return $this->endpoint('POST', '{{base_url}}/{{namespace}}/orders/:id/complete', $parameters, $options);
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
        return $this->endpoint('POST', '{{base_url}}/{{namespace}}/orders', $parameters, $options);
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
        return $this->endpoint('POST', '{{base_url}}/{{namespace}}/orders', $parameters, $options);
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
        return $this->endpoint('POST', '{{base_url}}/{{namespace}}/orders', $parameters, $options);
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
        return $this->endpoint('POST', '{{base_url}}/{{namespace}}/orders', $parameters, $options);
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
        return $this->endpoint('POST', '{{base_url}}/{{namespace}}/orders', $parameters, $options);
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
        return $this->endpoint('POST', '{{base_url}}/{{namespace}}/orders', $parameters, $options);
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
        return $this->endpoint('POST', '{{base_url}}/{{namespace}}/orders', $parameters, $options);
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
        return $this->endpoint('POST', '{{base_url}}/{{namespace}}/orders', $parameters, $options);
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
        return $this->endpoint('POST', '{{base_url}}/{{namespace}}/orders', $parameters, $options);
    }

    /**
     * Delete an Order.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function deleteOrder(array $parameters = [], array $options = [])
    {
        return $this->endpoint('DELETE', '{{base_url}}/{{namespace}}/orders/:id', $parameters, $options);
    }

    /**
     * Dispatch an Order.
     *
     * @param string|array<string, mixed> $idOrParameters
     * @param array<string, mixed> $parametersOrOptions
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function dispatchOrder($idOrParameters = [], array $parametersOrOptions = [], array $options = [])
    {
        if (is_array($idOrParameters)) {
            return $this->endpoint('PATCH', '{{base_url}}/{{namespace}}/orders/:id/dispatch', $idOrParameters, $parametersOrOptions);
        }

        $parametersOrOptions['id'] = $idOrParameters;
        return $this->endpoint('PATCH', '{{base_url}}/{{namespace}}/orders/:id/dispatch', $parametersOrOptions, $options);
    }

    /**
     * Get Editable Entity Fields.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function getEditableEntityFields(array $parameters = [], array $options = [])
    {
        return $this->endpoint('GET', '{{base_url}}/{{namespace}}/orders/:id/editable-entity-fields', $parameters, $options);
    }

    /**
     * Get Order Distance and Time.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function getOrderDistanceAndTime(array $parameters = [], array $options = [])
    {
        return $this->endpoint('GET', '{{base_url}}/{{namespace}}/orders/:id/distance-and-time', $parameters, $options);
    }

    /**
     * Get Order ETA.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function getOrderEta(array $parameters = [], array $options = [])
    {
        return $this->endpoint('GET', '{{base_url}}/{{namespace}}/orders/:id/eta', $parameters, $options);
    }

    /**
     * Get Order Next Activity.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function getOrderNextActivity(array $parameters = [], array $options = [])
    {
        return $this->endpoint('GET', '{{base_url}}/{{namespace}}/orders/:id/next-activity', $parameters, $options);
    }

    /**
     * Get Order Tracker.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function getOrderTracker(array $parameters = [], array $options = [])
    {
        return $this->endpoint('GET', '{{base_url}}/{{namespace}}/orders/:id/tracker', $parameters, $options);
    }

    /**
     * List Order Comments.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function listOrderComments(array $parameters = [], array $options = [])
    {
        return $this->endpoint('GET', '{{base_url}}/{{namespace}}/orders/:id/comments', $parameters, $options);
    }

    /**
     * List Order Proofs.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function listOrderProofs(array $parameters = [], array $options = [])
    {
        return $this->endpoint('GET', '{{base_url}}/{{namespace}}/orders/:id/proofs/:subjectId', $parameters, $options);
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
        return $this->endpoint('GET', '{{base_url}}/{{namespace}}/orders', $parameters, $options);
    }

    /**
     * Retrieve an Order.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function retrieveOrder(array $parameters = [], array $options = [])
    {
        return $this->endpoint('GET', '{{base_url}}/{{namespace}}/orders/{{order_id}}', $parameters, $options);
    }

    /**
     * Schedule an Order.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function scheduleOrder(array $parameters = [], array $options = [])
    {
        return $this->endpoint('PATCH', '{{base_url}}/{{namespace}}/orders/:id/schedule', $parameters, $options);
    }

    /**
     * Set Order Destination.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function setOrderDestination(array $parameters = [], array $options = [])
    {
        return $this->endpoint('PATCH', '{{base_url}}/{{namespace}}/orders/:id/set-destination/:placeId', $parameters, $options);
    }

    /**
     * Start an Order.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function startOrder(array $parameters = [], array $options = [])
    {
        return $this->endpoint('POST', '{{base_url}}/{{namespace}}/orders/:id/start', $parameters, $options);
    }

    /**
     * Update an Order.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function updateOrder(array $parameters = [], array $options = [])
    {
        return $this->endpoint('PUT', '{{base_url}}/{{namespace}}/orders/:id', $parameters, $options);
    }

    /**
     * Update Order Activity.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function updateOrderActivity(array $parameters = [], array $options = [])
    {
        return $this->endpoint('POST', '{{base_url}}/{{namespace}}/orders/:id/update-activity', $parameters, $options);
    }
}
