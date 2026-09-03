<?php

/** Run representative SDK success and failure paths against a disposable Fleetbase stack. */

declare(strict_types=1);

use Fleetbase\Sdk\Exception\AuthenticationException;
use Fleetbase\Sdk\Exception\ValidationException;
use Fleetbase\Sdk\Fleetbase;
use Fleetbase\Sdk\FleetbaseException;

require dirname(__DIR__) . '/vendor/autoload.php';

$baseUrl = getenv('FLEETBASE_CONTRACT_BASE_URL');
$apiKey = getenv('FLEETBASE_CONTRACT_API_KEY');
if (!is_string($baseUrl) || !is_string($apiKey) || $baseUrl === '' || $apiKey === '') {
    fail('FLEETBASE_CONTRACT_BASE_URL and FLEETBASE_CONTRACT_API_KEY are required.');
}

$fleetbase = new Fleetbase($apiKey, ['host' => $baseUrl, 'namespace' => 'v1']);
$createdId = null;

try {
    $organization = $fleetbase->organizations->getCurrentOrganization();
    if (!is_object($organization)) {
        fail('Current organization did not return an object.');
    }

    try {
        $fleetbase->places->createPlace(['body' => []]);
        fail('An invalid place unexpectedly passed validation.');
    } catch (ValidationException $exception) {
        if ($exception->getStatusCode() !== 422) {
            fail('Validation failure did not retain HTTP 422.');
        }
    }

    $created = $fleetbase->places->createPlace(['body' => [
        'name' => 'SDK Contract Fixture',
        'street1' => '1 Contract Test Way',
        'city' => 'Singapore',
        'country' => 'SG',
        'latitude' => 1.29027,
        'longitude' => 103.851959,
    ]]);
    $createdId = publicId($created);
    if ($createdId === null) {
        fail('Created place response had no public ID.');
    }

    $retrieved = $fleetbase->places->retrievePlace(['id' => $createdId]);
    if (publicId($retrieved) !== $createdId) {
        fail('Retrieved place did not match the created public ID.');
    }

    $fleetbase->places->deletePlace(['id' => $createdId]);
    $createdId = null;

    try {
        $fleetbase->places->retrievePlace(['id' => 'place_sdk_contract_missing']);
        fail('A missing place unexpectedly returned successfully.');
    } catch (FleetbaseException $exception) {
        if (!in_array($exception->getStatusCode(), [400, 404], true)) {
            fail('Not-found failure did not retain the Fleetbase API status.');
        }
        if (stripos($exception->getMessage(), 'not found') === false) {
            fail('Not-found failure did not retain the Fleetbase API message.');
        }
    }

    try {
        (new Fleetbase('flb_test_invalid_contract_key', ['host' => $baseUrl]))
            ->organizations
            ->getCurrentOrganization();
        fail('An invalid API key unexpectedly authenticated.');
    } catch (AuthenticationException $exception) {
        if ($exception->getStatusCode() !== 401) {
            fail('Authentication failure did not retain HTTP 401.');
        }
    }
} finally {
    if (is_string($createdId)) {
        try {
            $fleetbase->places->deletePlace(['id' => $createdId]);
        } catch (Throwable $exception) {
            fwrite(STDERR, "Unable to clean up the disposable place fixture.\n");
        }
    }
}

fwrite(STDOUT, "SDK live contract smoke passed: auth, validation, create, retrieve, delete, and not-found response.\n");

/** @param mixed $response */
function publicId($response): ?string
{
    if (!is_object($response)) {
        return null;
    }
    if (isset($response->id) && is_string($response->id)) {
        return $response->id;
    }
    if (isset($response->data) && is_object($response->data) && isset($response->data->id) && is_string($response->data->id)) {
        return $response->data->id;
    }

    return null;
}

function fail(string $message): void
{
    fwrite(STDERR, $message . "\n");
    exit(1);
}
