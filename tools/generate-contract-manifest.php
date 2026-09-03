<?php

declare(strict_types=1);

/**
 * Generate the in-scope SDK contract manifest from Postman Native Git YAML.
 *
 * Usage: php tools/generate-contract-manifest.php --postman=/path/to/postman
 *        --output=contracts/postman-manifest.json --ref=<commit>
 */

$options = getopt('', ['postman:', 'output:', 'ref:']);
foreach (['postman', 'output', 'ref'] as $required) {
    if (!isset($options[$required]) || !is_string($options[$required]) || $options[$required] === '') {
        fwrite(STDERR, sprintf("Missing required --%s option.\n", $required));
        exit(2);
    }
}

require dirname(__DIR__) . '/vendor/autoload.php';

$postmanRoot = realpath($options['postman']);
if ($postmanRoot === false || !is_dir($postmanRoot)) {
    fwrite(STDERR, sprintf("Postman checkout not found: %s\n", $options['postman']));
    exit(2);
}

$collectionsRoot = $postmanRoot . '/postman/collections';
$scopes = ['Fleetbase API', 'Fleetbase Core API'];
$requests = [];

foreach ($scopes as $collection) {
    $directory = $collectionsRoot . '/' . $collection;
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS)
    );

    foreach ($iterator as $file) {
        if (!$file->isFile() || substr($file->getFilename(), -13) !== '.request.yaml') {
            continue;
        }

        $contents = file_get_contents($file->getPathname());
        if (!is_string($contents)) {
            fwrite(STDERR, sprintf("Unable to read request: %s\n", $file->getPathname()));
            exit(1);
        }

        $relative = str_replace('\\', '/', substr($file->getPathname(), strlen($postmanRoot) + 1));
        $withinCollection = substr($relative, strlen('postman/collections/' . $collection . '/'));
        $segments = explode('/', $withinCollection);
        $requestName = preg_replace('/\.request\.yaml$/', '', array_pop($segments));
        $group = implode(' / ', $segments);
        $method = extractScalar($contents, 'method');
        $url = extractScalar($contents, 'url');
        $document = Symfony\Component\Yaml\Yaml::parse($contents);

        if ($method === null || $url === null || !is_string($requestName) || !is_array($document)) {
            fwrite(STDERR, sprintf("Request is missing method, URL, or name: %s\n", $relative));
            exit(1);
        }

        $requests[] = [
            'id' => slug($collection . '-' . $group . '-' . $requestName),
            'collection' => $collection,
            'group' => $group,
            'name' => $requestName,
            'method' => strtoupper($method),
            'url' => $url,
            'source' => $relative,
            'description' => is_string($document['description'] ?? null) ? $document['description'] : null,
            'authentication' => authentication($document),
            'request_fixture' => requestFixture($document),
            'response_fixtures' => responseFixtures($file->getPathname(), $document, $postmanRoot),
            'implementation' => null,
            'tests' => [],
            'status' => 'unmapped',
            'exception' => null,
        ];
    }
}

/** @param array<mixed, mixed> $document */
function authentication(array $document): string
{
    $headers = is_array($document['headers'] ?? null) ? $document['headers'] : [];
    if (array_key_exists('Customer-Token', $headers)) {
        return 'customer-token';
    }
    if (array_key_exists('Driver-Token', $headers)) {
        return 'driver-token';
    }

    return 'fleetbase-api-key';
}

/**
 * @param array<mixed, mixed> $document
 * @return array<string, mixed>
 */
function requestFixture(array $document): array
{
    $fixture = [
        'path_variables' => normalizeParameters($document['pathVariables'] ?? []),
        'query' => normalizeParameters($document['queryParams'] ?? []),
        'body_type' => null,
        'body' => null,
    ];
    $body = $document['body'] ?? null;
    if (!is_array($body)) {
        return $fixture;
    }

    $fixture['body_type'] = is_string($body['type'] ?? null) ? $body['type'] : null;
    $content = $body['content'] ?? null;
    if ($fixture['body_type'] === 'json' && is_string($content)) {
        $fixture['body'] = decodeJsonFixture($content);
    } elseif (is_array($content)) {
        $fixture['body'] = array_values($content);
    } elseif (is_string($content)) {
        $fixture['body'] = $content;
    }

    return $fixture;
}

/**
 * @param mixed $parameters
 * @return array<string, mixed>
 */
function normalizeParameters($parameters): array
{
    if (!is_array($parameters)) {
        return [];
    }
    $normalized = [];
    foreach ($parameters as $key => $value) {
        if (is_string($key)) {
            $normalized[$key] = $value;
        } elseif (is_array($value) && is_string($value['key'] ?? null)) {
            $normalized[$value['key']] = $value['value'] ?? null;
        }
    }
    return $normalized;
}

/** @return mixed */
function decodeJsonFixture(string $content)
{
    if (trim($content) === '') {
        return null;
    }
    $content = preg_replace_callback('/\{\{([^}]+)\}\}/', static function (array $matches): string {
        return trim($matches[1], '$') . '-fixture';
    }, $content) ?? $content;

    if (json_decode($content, true) === null && json_last_error() !== JSON_ERROR_NONE) {
        $content = preg_replace_callback('/(:\s*)([A-Za-z_$][A-Za-z0-9_$-]*-fixture)(?=\s*[,}])/', static function (array $matches): string {
            return $matches[1] . json_encode($matches[2], JSON_THROW_ON_ERROR);
        }, $content) ?? $content;
    }

    return json_decode($content, true, 512, JSON_THROW_ON_ERROR);
}

/**
 * @param array<mixed, mixed> $document
 * @return array<int, array{source: string, status: int|null}>
 */
function responseFixtures(string $requestPath, array $document, string $postmanRoot): array
{
    $examples = $document['examples'] ?? null;
    if (!is_string($examples) || $examples === '') {
        return [];
    }
    $directory = realpath(dirname($requestPath) . '/' . $examples);
    if ($directory === false || !is_dir($directory)) {
        return [];
    }

    $fixtures = [];
    foreach (glob($directory . '/*.example.yaml') ?: [] as $examplePath) {
        $example = Symfony\Component\Yaml\Yaml::parseFile($examplePath);
        $status = is_array($example) && is_int($example['response']['statusCode'] ?? null)
            ? $example['response']['statusCode']
            : null;
        $fixtures[] = [
            'source' => str_replace('\\', '/', substr($examplePath, strlen($postmanRoot) + 1)),
            'status' => $status,
        ];
    }
    usort($fixtures, static function (array $left, array $right): int {
        return strcmp($left['source'], $right['source']);
    });
    return $fixtures;
}

usort($requests, static function (array $left, array $right): int {
    return [$left['collection'], $left['group'], $left['name']] <=> [$right['collection'], $right['group'], $right['name']];
});

$methods = [];
$groups = [];
foreach ($requests as $request) {
    $methods[$request['method']] = ($methods[$request['method']] ?? 0) + 1;
    $groups[$request['collection'] . '/' . $request['group']] = true;
}
ksort($methods);

$manifest = [
    'schema_version' => 1,
    'source' => [
        'repository' => 'https://github.com/fleetbase/postman',
        'ref' => $options['ref'],
        'collections' => $scopes,
    ],
    'summary' => [
        'requests' => count($requests),
        'groups' => count($groups),
        'methods' => $methods,
        'mapped' => 0,
        'exceptions' => 0,
        'unmapped' => count($requests),
    ],
    'requests' => $requests,
];

$json = json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
if (!is_string($json)) {
    fwrite(STDERR, "Unable to encode contract manifest.\n");
    exit(1);
}

$outputDirectory = dirname($options['output']);
if (!is_dir($outputDirectory) && !mkdir($outputDirectory, 0777, true) && !is_dir($outputDirectory)) {
    fwrite(STDERR, sprintf("Unable to create output directory: %s\n", $outputDirectory));
    exit(1);
}

if (file_put_contents($options['output'], $json . "\n") === false) {
    fwrite(STDERR, sprintf("Unable to write manifest: %s\n", $options['output']));
    exit(1);
}

function extractScalar(string $yaml, string $key): ?string
{
    if (!preg_match('/^' . preg_quote($key, '/') . ':\\s*(?:"([^"]*)"|\'([^\']*)\'|([^\\r\\n#]+))/m', $yaml, $matches)) {
        return null;
    }

    foreach ([1, 2, 3] as $index) {
        if (isset($matches[$index]) && $matches[$index] !== '') {
            return trim($matches[$index]);
        }
    }

    return '';
}

function slug(string $value): string
{
    $value = strtolower($value);
    $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';
    return trim($value, '-');
}
