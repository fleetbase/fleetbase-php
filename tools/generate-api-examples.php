<?php

/** Generate one executable PHP SDK example for every locked Postman request. */

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

$options = getopt('', ['manifest:', 'output:']);
$manifestPath = is_string($options['manifest'] ?? null) ? $options['manifest'] : 'contracts/postman-manifest.json';
$outputPath = is_string($options['output'] ?? null) ? $options['output'] : 'docs/api-examples.md';
$contents = file_get_contents($manifestPath);
$manifest = is_string($contents) ? json_decode($contents, true) : null;
if (!is_array($manifest) || !is_array($manifest['requests'] ?? null)) {
    fail('Unable to read the endpoint contract manifest.');
}

$lines = [
    '# Fleetbase PHP SDK API examples',
    '',
    sprintf('These %d examples are generated from the locked official Postman contract. CI executes every fenced snippet against a hermetic PSR-18 transport.', count($manifest['requests'])),
    '',
    'Create `$fleetbase` once as shown in the README, then use the relevant service call below. Fixture identifiers and payloads are illustrative; replace them with values from your application.',
];
$currentGroup = null;

foreach ($manifest['requests'] as $request) {
    if (!is_array($request)) {
        fail('The endpoint contract contains an invalid request.');
    }
    $group = requiredString($request, 'group');
    if ($group !== $currentGroup) {
        $lines[] = '';
        $lines[] = '## ' . $group;
        $currentGroup = $group;
    }

    $implementation = explode('::', requiredString($request, 'implementation'), 2);
    $method = $implementation[1] ?? null;
    if (!is_string($method) || $method === '') {
        fail('A request has no callable implementation method.');
    }
    $property = serviceProperty($group);
    if (!property_exists(Fleetbase\Sdk\Fleetbase::class, $property)) {
        fail(sprintf('Fleetbase has no service property for group %s.', $group));
    }

    [$parameters, $callOptions] = arguments($request);
    $call = sprintf(
        "\$result = \$fleetbase->%s->%s(\n    %s,\n    %s\n);",
        $property,
        $method,
        exported($parameters, 1),
        exported($callOptions, 1)
    );

    $lines[] = '';
    $lines[] = '### ' . requiredString($request, 'name');
    $description = $request['description'] ?? null;
    if (is_string($description) && trim($description) !== '') {
        $lines[] = '';
        $normalizedDescription = trim(preg_replace('/\s+/', ' ', $description) ?? $description);
        $lines[] = str_replace('FleetOps', 'Fleet-Ops', $normalizedDescription);
    }
    $lines[] = '';
    $lines[] = sprintf('`%s %s`', requiredString($request, 'method'), requiredString($request, 'url'));
    $lines[] = '';
    $lines[] = '```php';
    $lines[] = $call;
    $lines[] = '```';
}

$directory = dirname($outputPath);
if (!is_dir($directory) && !mkdir($directory, 0777, true) && !is_dir($directory)) {
    fail('Unable to create the API examples directory.');
}
if (file_put_contents($outputPath, implode("\n", $lines) . "\n") === false) {
    fail('Unable to write the API examples.');
}

printf("Generated %d executable API examples.\n", count($manifest['requests']));

/** @param array<string, mixed> $request @return array{array<string, mixed>, array<string, mixed>} */
function arguments(array $request): array
{
    $fixture = is_array($request['request_fixture'] ?? null) ? $request['request_fixture'] : [];
    $pathVariables = is_array($fixture['path_variables'] ?? null) ? $fixture['path_variables'] : [];
    $parameters = [];
    preg_match_all('/(?:\{\{([^}]+)\}\}|:([A-Za-z][A-Za-z0-9_-]*))/', requiredString($request, 'url'), $matches, PREG_SET_ORDER);
    foreach ($matches as $match) {
        $name = ($match[1] ?? '') !== '' ? $match[1] : ($match[2] ?? '');
        if ($name === '' || in_array($name, ['base_url', 'namespace'], true)) {
            continue;
        }
        $parameters[$name] = normalized($pathVariables[$name] ?? $name . '-fixture');
    }

    $callOptions = [];
    $query = normalized($fixture['query'] ?? []);
    if (is_array($query) && $query !== []) {
        $callOptions['query'] = $query;
    }
    $body = normalized($fixture['body'] ?? null);
    if (($fixture['body_type'] ?? null) === 'json' && is_array($body)) {
        $parameters['body'] = $body;
    } elseif (($fixture['body_type'] ?? null) === 'formdata' && is_array($body)) {
        foreach ($body as $part) {
            if (!is_array($part) || !is_string($part['key'] ?? null)) {
                continue;
            }
            $value = normalized($part['value'] ?? '');
            $callOptions['multipart'][] = [
                'name' => $part['key'],
                'contents' => ($part['type'] ?? null) === 'file'
                    ? 'replace-with-file-contents'
                    : (is_scalar($value) ? (string) $value : ''),
            ];
        }
    }

    return [$parameters, $callOptions];
}

/** @param mixed $value @return mixed */
function normalized($value)
{
    if (is_array($value)) {
        foreach ($value as $key => $item) {
            $value[$key] = normalized($item);
        }
        return $value;
    }
    if (!is_string($value)) {
        return $value;
    }
    return preg_replace_callback('/\{\{([^}]+)\}\}/', static function (array $matches): string {
        return trim($matches[1], '$') . '-fixture';
    }, $value) ?? $value;
}

/** @param mixed $value */
function exported($value, int $level): string
{
    if (!is_array($value)) {
        return var_export($value, true);
    }
    if ($value === []) {
        return '[]';
    }

    $lines = ['['];
    $isList = array_keys($value) === range(0, count($value) - 1);
    foreach ($value as $key => $item) {
        $prefix = $isList ? '' : var_export($key, true) . ' => ';
        $lines[] = str_repeat('    ', $level + 1) . $prefix . exported($item, $level + 1) . ',';
    }
    $lines[] = str_repeat('    ', $level) . ']';

    return implode("\n", $lines);
}

function serviceProperty(string $group): string
{
    $classified = Doctrine\Inflector\InflectorFactory::create()->build()->classify(strtolower($group));
    return lcfirst($classified);
}

/** @param array<string, mixed> $data */
function requiredString(array $data, string $key): string
{
    $value = $data[$key] ?? null;
    if (!is_string($value) || $value === '') {
        fail(sprintf('Missing endpoint contract field: %s.', $key));
    }
    return $value;
}

function fail(string $message): void
{
    fwrite(STDERR, $message . "\n");
    exit(1);
}
