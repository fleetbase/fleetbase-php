<?php

declare(strict_types=1);

/** Generate the human-readable 220-request coverage matrix. */

$options = getopt('', ['manifest:', 'output:']);
$manifestPath = is_string($options['manifest'] ?? null) ? $options['manifest'] : 'contracts/postman-manifest.json';
$outputPath = is_string($options['output'] ?? null) ? $options['output'] : 'docs/api-coverage.md';
$contents = file_get_contents($manifestPath);
$manifest = is_string($contents) ? json_decode($contents, true) : null;
if (!is_array($manifest) || !is_array($manifest['requests'] ?? null)) {
    fwrite(STDERR, "Unable to read the contract manifest.\n");
    exit(1);
}

$ref = $manifest['source']['ref'] ?? null;
if (!is_string($ref) || $ref === '') {
    fwrite(STDERR, "The contract manifest has no source ref.\n");
    exit(1);
}

$lines = [
    '# Fleetbase PHP SDK API coverage',
    '',
    sprintf('Generated from the locked Postman contract at `%s`. This matrix contains exactly %d requests.', $ref, count($manifest['requests'])),
    '',
    '“Mapped” means the public method and deterministic request fixture test exist. Live contract verification remains a separate isolated-stack release gate.',
    '',
    '| Collection / request | Method and path | Authentication | SDK service / action | Request fixture | Response fixture | Error fixture | Deterministic test | Live contract | Status |',
    '|---|---|---|---|---|---|---|---|---|---|',
];

foreach ($manifest['requests'] as $request) {
    if (!is_array($request)) {
        continue;
    }
    $source = stringValue($request['source'] ?? null);
    $sourceLink = sprintf(
        '[%s / %s](https://github.com/fleetbase/postman/blob/%s/%s)',
        escaped(stringValue($request['collection'] ?? null)),
        escaped(stringValue($request['name'] ?? null)),
        rawurlencode($ref),
        pathUrl($source)
    );
    $responses = [];
    foreach (is_array($request['response_fixtures'] ?? null) ? $request['response_fixtures'] : [] as $fixture) {
        if (!is_array($fixture)) {
            continue;
        }
        $fixtureSource = stringValue($fixture['source'] ?? null);
        $status = is_int($fixture['status'] ?? null) ? (string) $fixture['status'] : 'example';
        $responses[] = sprintf('[%s](https://github.com/fleetbase/postman/blob/%s/%s)', $status, rawurlencode($ref), pathUrl($fixtureSource));
    }
    $requestFixture = is_array($request['request_fixture'] ?? null) ? $request['request_fixture'] : [];
    $fixtureSummary = [];
    if (is_array($requestFixture['path_variables'] ?? null) && $requestFixture['path_variables'] !== []) {
        $fixtureSummary[] = 'path';
    }
    if (is_array($requestFixture['query'] ?? null) && $requestFixture['query'] !== []) {
        $fixtureSummary[] = 'query';
    }
    if (is_string($requestFixture['body_type'] ?? null)) {
        $fixtureSummary[] = $requestFixture['body_type'] . ' body';
    }
    if ($fixtureSummary === []) {
        $fixtureSummary[] = 'path/method only';
    }

    $lines[] = '| ' . implode(' | ', [
        $sourceLink,
        '`' . escaped(stringValue($request['method'] ?? null) . ' ' . stringValue($request['url'] ?? null)) . '`',
        escaped(stringValue($request['authentication'] ?? null)),
        '`' . escaped(stringValue($request['implementation'] ?? null)) . '`',
        escaped(implode(', ', $fixtureSummary)) . ' ([source](https://github.com/fleetbase/postman/blob/' . rawurlencode($ref) . '/' . pathUrl($source) . '))',
        $responses !== [] ? implode(', ', $responses) : 'No official example',
        '[HTTP status mapping](../tests/HttpClientTest.php)',
        '[endpoint fixture](../tests/Contract/EndpointContractTest.php)',
        'Official collection through SDK bridge',
        escaped(stringValue($request['status'] ?? null)),
    ]) . ' |';
}

$outputDirectory = dirname($outputPath);
if (!is_dir($outputDirectory) && !mkdir($outputDirectory, 0777, true) && !is_dir($outputDirectory)) {
    fwrite(STDERR, "Unable to create API coverage output directory.\n");
    exit(1);
}
if (file_put_contents($outputPath, implode("\n", $lines) . "\n") === false) {
    fwrite(STDERR, "Unable to write API coverage matrix.\n");
    exit(1);
}

printf("Generated API coverage matrix with %d requests.\n", count($manifest['requests']));

/** @param mixed $value */
function stringValue($value): string
{
    return is_string($value) ? $value : '';
}

function escaped(string $value): string
{
    return str_replace(["\r", "\n", '|'], [' ', ' ', '\\|'], $value);
}

function pathUrl(string $path): string
{
    return implode('/', array_map('rawurlencode', explode('/', $path)));
}
