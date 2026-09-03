<?php

declare(strict_types=1);

/**
 * Validate the generated Postman contract manifest.
 *
 * Add --require-complete to fail when a request is not mapped or approved.
 */

$options = getopt('', ['manifest:', 'lock:', 'require-complete']);
$manifestPath = isset($options['manifest']) && is_string($options['manifest'])
    ? $options['manifest']
    : 'contracts/postman-manifest.json';
$lockPath = isset($options['lock']) && is_string($options['lock'])
    ? $options['lock']
    : 'contracts/contract-lock.json';

$manifest = readJson($manifestPath);
$lock = readJson($lockPath);
$autoload = dirname(__DIR__) . '/vendor/autoload.php';
if (is_file($autoload)) {
    require $autoload;
}
$errors = [];
$expectedRequests = $lock['sources']['postman']['expected_requests'] ?? null;
$expectedGroups = $lock['sources']['postman']['expected_groups'] ?? null;
$expectedRef = $lock['sources']['postman']['commit'] ?? null;
$requests = $manifest['requests'] ?? [];

if (!is_array($requests) || count($requests) !== $expectedRequests) {
    $errors[] = sprintf('Expected %s requests, found %d', (string) $expectedRequests, is_array($requests) ? count($requests) : 0);
}
if (($manifest['summary']['groups'] ?? null) !== $expectedGroups) {
    $errors[] = sprintf('Expected %s groups, found %s', (string) $expectedGroups, (string) ($manifest['summary']['groups'] ?? 'missing'));
}
if (($manifest['source']['ref'] ?? null) !== $expectedRef) {
    $errors[] = 'Manifest source ref does not match the contract lock';
}

$ids = [];
$sources = [];
$counts = ['mapped' => 0, 'exceptions' => 0, 'unmapped' => 0];
foreach ($requests as $index => $request) {
    foreach (['id', 'collection', 'group', 'name', 'method', 'url', 'source', 'status'] as $field) {
        if (!isset($request[$field]) || !is_string($request[$field]) || $request[$field] === '') {
            $errors[] = sprintf('Request %d has invalid %s', $index + 1, $field);
        }
    }

    $id = $request['id'] ?? '';
    $source = $request['source'] ?? '';
    if (isset($ids[$id])) {
        $errors[] = sprintf('Duplicate request id: %s', $id);
    }
    if (isset($sources[$source])) {
        $errors[] = sprintf('Duplicate request source: %s', $source);
    }
    $ids[$id] = true;
    $sources[$source] = true;

    $signature = $request['sdk_signature'] ?? null;
    if (!is_array($signature)) {
        $errors[] = sprintf('Request %s has no SDK signature metadata', $id);
    } else {
        $pathParameters = $signature['path_parameters'] ?? null;
        $requestData = $signature['request_data'] ?? null;
        $contractPayload = $signature['contract_payload'] ?? null;
        if (!is_array($pathParameters) || array_filter($pathParameters, 'is_string') !== $pathParameters) {
            $errors[] = sprintf('Request %s has invalid SDK path parameters', $id);
        } else {
            preg_match_all('/\{\{([^}]+)\}\}|:([A-Za-z][A-Za-z0-9_-]*)/', (string) ($request['url'] ?? ''), $matches, PREG_SET_ORDER);
            $expectedPathParameters = array_map(static function (array $match): string {
                return $match[1] !== '' ? $match[1] : $match[2];
            }, $matches);
            $expectedPathParameters = array_values(array_filter($expectedPathParameters, static function (string $name): bool {
                return !in_array($name, ['base_url', 'namespace'], true);
            }));
            if ($pathParameters !== $expectedPathParameters) {
                $errors[] = sprintf('Request %s SDK path parameter order does not match its URL', $id);
            }
        }
        if (!in_array($requestData, ['body', 'query', 'multipart'], true)) {
            $errors[] = sprintf('Request %s has invalid SDK request data placement', $id);
        }
        if (!in_array($contractPayload, ['none', 'json', 'raw', 'multipart', 'query'], true)) {
            $errors[] = sprintf('Request %s has invalid SDK contract payload', $id);
        }
        if (($signature['legacy_envelope'] ?? null) !== true) {
            $errors[] = sprintf('Request %s does not preserve the legacy SDK envelope', $id);
        }
    }

    $status = $request['status'] ?? '';
    if ($status === 'complete') {
        ++$counts['mapped'];
        if (!is_string($request['implementation'] ?? null) || $request['implementation'] === '') {
            $errors[] = sprintf('Complete request %s has no implementation', $id);
        } else {
            $implementation = explode('::', $request['implementation'], 2);
            $class = $implementation[0] ?? '';
            $method = $implementation[1] ?? '';
            if (!class_exists($class) || !method_exists($class, $method)) {
                $errors[] = sprintf('Complete request %s points to missing implementation %s', $id, $request['implementation']);
            }
        }
        if (!is_array($request['tests'] ?? null) || $request['tests'] === []) {
            $errors[] = sprintf('Complete request %s has no tests', $id);
        } else {
            foreach ($request['tests'] as $test) {
                if (!is_string($test)) {
                    $errors[] = sprintf('Complete request %s has an invalid test reference', $id);
                    continue;
                }
                $testReference = explode('::', $test, 2);
                $testFile = $testReference[0] ?? '';
                $testMethod = $testReference[1] ?? '';
                $testContents = is_file($testFile) ? file_get_contents($testFile) : false;
                if (!is_string($testContents) || !preg_match('/function\s+' . preg_quote($testMethod, '/') . '\s*\(/', $testContents)) {
                    $errors[] = sprintf('Complete request %s points to missing test %s', $id, $test);
                }
            }
        }
    } elseif ($status === 'exception') {
        ++$counts['exceptions'];
        if (!is_string($request['exception'] ?? null) || $request['exception'] === '') {
            $errors[] = sprintf('Exception request %s has no rationale', $id);
        }
    } elseif ($status === 'unmapped' || $status === 'partial') {
        ++$counts['unmapped'];
    } else {
        $errors[] = sprintf('Request %s has unsupported status %s', $id, (string) $status);
    }
}

foreach ($counts as $field => $value) {
    if (($manifest['summary'][$field] ?? null) !== $value) {
        $errors[] = sprintf('Summary %s is %s, calculated %d', $field, (string) ($manifest['summary'][$field] ?? 'missing'), $value);
    }
}

if (array_key_exists('require-complete', $options) && $counts['unmapped'] !== 0) {
    $errors[] = sprintf('%d requests remain unmapped or partial', $counts['unmapped']);
}

if ($errors !== []) {
    fwrite(STDERR, "Contract manifest check failed:\n- " . implode("\n- ", $errors) . "\n");
    exit(1);
}

printf(
    "Contract manifest passed: %d requests, %d mapped, %d exceptions, %d unmapped.\n",
    count($requests),
    $counts['mapped'],
    $counts['exceptions'],
    $counts['unmapped']
);

/**
 * @return array<string, mixed>
 */
function readJson(string $path): array
{
    $contents = file_get_contents($path);
    if (!is_string($contents)) {
        fwrite(STDERR, sprintf("Unable to read JSON: %s\n", $path));
        exit(2);
    }

    $data = json_decode($contents, true);
    if (!is_array($data)) {
        fwrite(STDERR, sprintf("Invalid JSON: %s\n", $path));
        exit(2);
    }

    return $data;
}
