<?php

declare(strict_types=1);

namespace Fleetbase\Sdk\Test\Fleetbase;

use Fleetbase\Sdk\Exception\ValidationException;
use Fleetbase\Sdk\Resources\Inspection;
use Fleetbase\Sdk\Resources\InspectionForm;
use Fleetbase\Sdk\Services\InspectionFormService;
use Fleetbase\Sdk\Services\InspectionService;
use Fleetbase\Sdk\Services\VehicleService;
use Fleetbase\Sdk\Test\TestCase;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;

final class InspectionTest extends TestCase
{
    public function testSubmitPreservesTypedAnswersAndCallerIdempotencyKey(): void
    {
        $payload = [
            'inspection_form' => 'iform_test',
            'driver' => 'driver_test',
            'custom_field_values' => [
                ['custom_field' => 'field_brakes', 'value_type' => 'object', 'value' => ['passed' => false, 'comments' => 'Soft pedal', 'photos' => ['data:image/png;base64,test']]],
                ['custom_field' => 'field_meter', 'value_type' => 'number', 'value' => 120400],
            ],
        ];
        $client = $this->mockHttpClient([
            new Response(201, [], '{"id":"inspection_test","custom_field_values":[],"item_results":[]}'),
            new Response(200, [], '{"id":"inspection_test","custom_field_values":[],"item_results":[]}'),
        ]);
        $service = new InspectionService($client);
        $options = ['headers' => ['Idempotency-Key' => 'inspection-offline-123']];
        $first = $service->submitInspection($payload, $options);
        $replay = $service->submitInspection(['body' => $payload], $options);
        self::assertEquals($first, $replay);
        foreach ($this->history as $transaction) {
            self::assertIsArray($transaction);
            $request = $transaction['request'] ?? null;
            self::assertInstanceOf(RequestInterface::class, $request);
            self::assertSame('POST', $request->getMethod());
            self::assertSame('/v1/inspections', $request->getUri()->getPath());
            self::assertSame('inspection-offline-123', $request->getHeaderLine('Idempotency-Key'));
            self::assertSame($payload, json_decode((string) $request->getBody(), true));
        }
    }

    public function testResourceHelpersUseSeparateFormAndSubmissionNamespaces(): void
    {
        $client = $this->mockHttpClient([
            new Response(200, [], '[{"id":"iform_test","grouped_fields":[{"name":"Safety","fields":[]}]}]'),
            new Response(200, [], '{"id":"inspection_test","item_results":[],"files":[]}'),
        ]);
        $forms = (new InspectionFormService($client))->query(['vehicle' => 'vehicle_test']);
        self::assertIsArray($forms);
        self::assertInstanceOf(InspectionForm::class, $forms[0]);
        self::assertEquals([(object) ['name' => 'Safety', 'fields' => []]], $forms[0]->getAttribute('grouped_fields'));
        $inspection = (new InspectionService($client))->findRecord('inspection_test');
        self::assertInstanceOf(Inspection::class, $inspection);
        foreach ($this->history as $index => $transaction) {
            self::assertIsArray($transaction);
            $request = $transaction['request'] ?? null;
            self::assertInstanceOf(RequestInterface::class, $request);
            self::assertSame($index === 0 ? '/v1/inspection-forms' : '/v1/inspections/inspection_test', $request->getUri()->getPath());
        }
    }

    public function testVehicleHistoryAcceptsPositionalIdAndDirectFilters(): void
    {
        $service = new VehicleService($this->mockHttpClient([new Response(200, [], '[]')]));
        $service->listVehicleInspections('vehicle_test', ['status' => 'open', 'limit' => 10]);
        $transaction = $this->history[0];
        self::assertIsArray($transaction);
        $request = $transaction['request'] ?? null;
        self::assertInstanceOf(RequestInterface::class, $request);
        self::assertSame('/v1/vehicles/vehicle_test/inspections', $request->getUri()->getPath());
        parse_str($request->getUri()->getQuery(), $query);
        self::assertSame(['status' => 'open', 'limit' => '10'], $query);
    }

    public function testSubmissionValidationErrorIsPreserved(): void
    {
        $service = new InspectionService($this->mockHttpClient([
            new Response(422, [], '{"error":"A comment is required when this inspection field fails."}'),
        ]));
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('A comment is required');
        $service->submitInspection(['inspection_form' => 'iform_test', 'driver' => 'driver_test']);
    }
}
