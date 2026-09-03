<?php

/** Generate one executable PHP SDK example for every locked Postman request. */

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

$options = getopt('', ['manifest:', 'output:', 'catalog-output:']);
$manifestPath = is_string($options['manifest'] ?? null) ? $options['manifest'] : 'contracts/postman-manifest.json';
$outputPath = is_string($options['output'] ?? null) ? $options['output'] : 'docs/api-examples.md';
$catalogPath = is_string($options['catalog-output'] ?? null) ? $options['catalog-output'] : 'contracts/php-sdk-examples.json';
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
$catalog = [
    'schema_version' => 1,
    'generated_from' => $manifest['source'] ?? [],
    'package' => 'fleetbase/fleetbase-php',
    'examples' => [],
];

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

    [$arguments, $variables] = arguments($request);
    $call = renderCall($property, $method, $arguments);

    $id = requiredString($request, 'id');
    $catalog['examples'][$id] = [
        'collection' => requiredString($request, 'collection'),
        'group' => $group,
        'name' => requiredString($request, 'name'),
        'implementation' => requiredString($request, 'implementation'),
        'variables' => $variables,
        'call' => $call,
        'code' => standaloneCode($variables, $call),
    ];

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
$catalogDirectory = dirname($catalogPath);
if (!is_dir($catalogDirectory) && !mkdir($catalogDirectory, 0777, true) && !is_dir($catalogDirectory)) {
    fail('Unable to create the API example catalog directory.');
}
$catalogJson = json_encode($catalog, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
if (!is_string($catalogJson) || file_put_contents($catalogPath, $catalogJson . "\n") === false) {
    fail('Unable to write the API example catalog.');
}

printf("Generated %d executable API examples and the website catalog.\n", count($manifest['requests']));

final class PhpExpression
{
    public string $code;

    public function __construct(string $code)
    {
        $this->code = $code;
    }
}

/** @param array<string, mixed> $request @return array{array<int, mixed>, array<string, mixed>} */
function arguments(array $request): array
{
    $fixture = is_array($request['request_fixture'] ?? null) ? $request['request_fixture'] : [];
    $pathVariables = is_array($fixture['path_variables'] ?? null) ? $fixture['path_variables'] : [];
    $signature = is_array($request['sdk_signature'] ?? null) ? $request['sdk_signature'] : [];
    $pathParameters = is_array($signature['path_parameters'] ?? null) ? $signature['path_parameters'] : [];
    $arguments = [];
    $variables = [];
    foreach ($pathParameters as $name) {
        if (!is_string($name) || $name === '') {
            fail('An SDK signature has an invalid path parameter.');
        }
        $variable = variableName($name, requiredString($request, 'group'));
        $fixtureValue = normalized($pathVariables[$name] ?? $name . '-fixture');
        $variables[$variable] = $fixtureValue === '' ? $name . '-fixture' : $fixtureValue;
        $arguments[] = new PhpExpression('$' . $variable);
    }

    $query = normalized($fixture['query'] ?? []);
    $body = normalized($fixture['body'] ?? null);
    $data = [];
    if (($fixture['body_type'] ?? null) === 'json' && is_array($body)) {
        $data = $body;
    } elseif (($fixture['body_type'] ?? null) === 'text' && is_string($body)) {
        $decoded = json_decode($body, true);
        if (is_array($decoded)) {
            $data = $decoded;
        }
    } elseif (($fixture['body_type'] ?? null) === 'formdata' && is_array($body)) {
        foreach ($body as $part) {
            if (!is_array($part) || !is_string($part['key'] ?? null)) {
                continue;
            }
            $value = normalized($part['value'] ?? '');
            $data[] = [
                'name' => $part['key'],
                'contents' => ($part['type'] ?? null) === 'file'
                    ? 'replace-with-file-contents'
                    : (is_scalar($value) ? (string) $value : ''),
            ];
        }
    } elseif (is_array($query)) {
        $data = $query;
    }

    if ($data !== []) {
        $arguments[] = $data;
    }

    return [$arguments, $variables];
}

function variableName(string $pathParameter, string $group): string
{
    $source = $pathParameter === 'id'
        ? Doctrine\Inflector\InflectorFactory::create()->build()->singularize($group) . '_id'
        : $pathParameter;
    $source = preg_replace('/([a-z0-9])([A-Z])/', '$1 $2', $source) ?? $source;
    $source = preg_replace('/[^A-Za-z0-9]+/', ' ', $source) ?? $source;
    $name = lcfirst(Doctrine\Inflector\InflectorFactory::create()->build()->classify(strtolower(trim($source))));
    return $name !== '' ? $name : 'resourceId';
}

/** @param array<int, mixed> $arguments */
function renderCall(string $property, string $method, array $arguments): string
{
    $prefix = sprintf('$result = $fleetbase->%s->%s', $property, $method);
    if ($arguments === []) {
        return $prefix . '();';
    }

    $containsArray = false;
    foreach ($arguments as $argument) {
        if (is_array($argument)) {
            $containsArray = true;
            break;
        }
    }
    if (!$containsArray) {
        return $prefix . '(' . implode(', ', array_map(static function ($argument): string {
            return exported($argument, 0);
        }, $arguments)) . ');';
    }

    $rendered = [];
    foreach ($arguments as $argument) {
        $rendered[] = '    ' . exported($argument, 1);
    }
    return $prefix . "(\n" . implode(",\n", $rendered) . "\n);";
}

/** @param array<string, mixed> $variables */
function standaloneCode(array $variables, string $call): string
{
    $lines = [
        '<?php',
        '',
        '$fleetbase = new \\Fleetbase\\Sdk\\Fleetbase(\'flb_live_…\');',
    ];
    foreach ($variables as $name => $value) {
        $lines[] = '$' . $name . ' = ' . exported($value, 0) . ';';
    }
    $lines[] = '';
    $lines[] = $call;
    return implode("\n", $lines);
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
    if ($value instanceof PhpExpression) {
        return $value->code;
    }
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
