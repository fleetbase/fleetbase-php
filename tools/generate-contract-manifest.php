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
        $withinCollection = substr($relative, strlen('postman/collections/' . $collection . '/') );
        $segments = explode('/', $withinCollection);
        $requestName = preg_replace('/\.request\.yaml$/', '', array_pop($segments));
        $group = implode(' / ', $segments);
        $method = extractScalar($contents, 'method');
        $url = extractScalar($contents, 'url');

        if ($method === null || $url === null || !is_string($requestName)) {
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
            'implementation' => null,
            'tests' => [],
            'status' => 'unmapped',
            'exception' => null,
        ];
    }
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
