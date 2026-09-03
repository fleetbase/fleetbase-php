<?php

/** Route the official Postman contract through the exact PHP SDK methods. */

declare(strict_types=1);

use Fleetbase\Sdk\Fleetbase;
use Fleetbase\Sdk\Service;
use Psr\Http\Message\ResponseInterface;

require dirname(__DIR__) . '/vendor/autoload.php';

$target = getenv('FLEETBASE_CONTRACT_TARGET_URL') ?: 'http://127.0.0.1:8000';
$namespace = trim(getenv('FLEETBASE_CONTRACT_NAMESPACE') ?: 'v1', '/');
$statePath = getenv('FLEETBASE_CONTRACT_STATE') ?: dirname(__DIR__) . '/build/live-contract/state.json';
$requestMethod = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
$requestUri = (string) ($_SERVER['REQUEST_URI'] ?? '/');
$path = (string) (parse_url($requestUri, PHP_URL_PATH) ?: '/');

if ($path === '/__sdk_contract_health') {
    header('Content-Type: application/json');
    echo "{\"status\":\"ok\"}\n";
    return;
}

$headers = requestHeaders();
$authorization = headerValue($headers, 'Authorization');
$apiKey = preg_match('/^Bearer\s+(.+)$/i', $authorization, $matches) === 1
    ? trim($matches[1])
    : 'sdk-contract-unauthenticated';
$relativePath = relativePath($path, $namespace);
$isPostmanRequest = stripos(headerValue($headers, 'User-Agent'), 'PostmanRuntime') !== false;

$fleetbase = new Fleetbase($apiKey, ['host' => $target, 'namespace' => $namespace]);

try {
    if (!$isPostmanRequest) {
        $fleetbase->client->request($requestMethod, $relativePath, requestData(), requestOptions($headers));
        emitResponse($fleetbase->client->getLastPsrResponse());
        return;
    }

    $request = reserveContractRequest($statePath, $requestMethod, $relativePath);
    $implementation = explode('::', requiredString($request, 'implementation'), 2);
    $serviceClass = $implementation[0] ?? '';
    $serviceMethod = $implementation[1] ?? '';
    $service = resolveService($fleetbase, $serviceClass);
    [$parameters, $options] = invocation($request, $relativePath, $headers);

    try {
        $service->{$serviceMethod}($parameters, $options);
    } catch (Throwable $exception) {
        $response = $fleetbase->client->getLastPsrResponse();
        if (!$response instanceof ResponseInterface) {
            throw $exception;
        }
    }

    $response = $fleetbase->client->getLastPsrResponse();
    recordResult($statePath, requiredString($request, 'id'), $response->getStatusCode());
    emitResponse($response);
} catch (Throwable $exception) {
    http_response_code(502);
    header('Content-Type: application/json');
    echo json_encode(['errors' => ['SDK contract bridge failure: ' . $exception->getMessage()]], JSON_UNESCAPED_SLASHES) . "\n";
}

/** @return array<string, string> */
function requestHeaders(): array
{
    $values = function_exists('getallheaders') ? getallheaders() : [];
    $headers = [];
    foreach ($values as $name => $value) {
        if (is_string($name) && is_string($value)) {
            $headers[$name] = $value;
        }
    }
    return $headers;
}

/** @param array<string, string> $headers */
function headerValue(array $headers, string $wanted): string
{
    foreach ($headers as $name => $value) {
        if (strcasecmp($name, $wanted) === 0) {
            return $value;
        }
    }
    return '';
}

function relativePath(string $path, string $namespace): string
{
    $prefix = '/' . $namespace;
    if ($path === $prefix) {
        return '';
    }
    if (strpos($path, $prefix . '/') === 0) {
        return ltrim(substr($path, strlen($prefix)), '/');
    }
    return ltrim($path, '/');
}

/** @return array<string, mixed> */
function requestData(): array
{
    if ($_GET !== []) {
        $data = [];
        foreach ($_GET as $key => $value) {
            if (is_string($key)) {
                $data[$key] = $value;
            }
        }
        return $data;
    }
    $raw = (string) file_get_contents('php://input');
    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : [];
}

/** @param array<string, string> $headers @return array<string, mixed> */
function requestOptions(array $headers): array
{
    $forwarded = [];
    foreach ($headers as $name => $value) {
        if (!in_array(strtolower($name), ['host', 'content-length', 'connection', 'authorization', 'user-agent'], true)) {
            $forwarded[$name] = $value;
        }
    }
    return $forwarded === [] ? [] : ['headers' => $forwarded];
}

/** @return array<string, mixed> */
function reserveContractRequest(string $statePath, string $method, string $path): array
{
    $manifestContents = file_get_contents(dirname(__DIR__) . '/contracts/postman-manifest.json');
    $manifest = is_string($manifestContents) ? json_decode($manifestContents, true) : null;
    if (!is_array($manifest) || !is_array($manifest['requests'] ?? null)) {
        throw new RuntimeException('Unable to read the SDK contract manifest.');
    }

    $handle = stateHandle($statePath);
    $state = readState($handle);
    $used = is_array($state['requests'] ?? null) ? $state['requests'] : [];
    $matches = [];
    $position = 0;
    foreach ($manifest['requests'] as $request) {
        if (!is_array($request)) {
            continue;
        }
        if (requiredString($request, 'method') !== $method || matchTemplate(requiredString($request, 'url'), $path) === null) {
            continue;
        }
        $matches[] = [
            'request' => $request,
            'specificity' => templateSpecificity(requiredString($request, 'url')),
            'position' => $position++,
        ];
    }

    usort($matches, static function (array $left, array $right): int {
        $specificity = $right['specificity'] <=> $left['specificity'];
        return $specificity !== 0 ? $specificity : $left['position'] <=> $right['position'];
    });

    foreach ($matches as $match) {
        $request = $match['request'];
        if (isset($used[$request['id'] ?? ''])) {
            continue;
        }
        $id = requiredString($request, 'id');
        $state['requests'][$id] = ['attempted' => true, 'attempts' => 1, 'status' => null];
        writeState($handle, $state);
        return $request;
    }

    if ($matches !== []) {
        $request = $matches[0]['request'];
        $id = requiredString($request, 'id');
        $previous = is_array($used[$id] ?? null) ? $used[$id] : [];
        $attempts = is_int($previous['attempts'] ?? null) ? $previous['attempts'] + 1 : 2;
        $state['requests'][$id] = [
            'attempted' => true,
            'attempts' => $attempts,
            'status' => is_int($previous['status'] ?? null) ? $previous['status'] : null,
        ];
        writeState($handle, $state);
        return $request;
    }

    fclose($handle);
    throw new RuntimeException(sprintf('No %s contract matches %s.', $method, $path));
}

function templateSpecificity(string $template): int
{
    $template = (string) preg_replace('#^\{\{base_url\}\}/\{\{namespace\}\}/?#i', '', $template);
    $segments = explode('/', trim(explode('?', $template, 2)[0], '/'));
    $specificity = 0;
    foreach ($segments as $segment) {
        if (preg_match('/^:[A-Za-z][A-Za-z0-9_-]*$/', $segment) !== 1
            && preg_match('/^\{\{[^}]+\}\}$/', $segment) !== 1) {
            $specificity++;
        }
    }
    return $specificity;
}

/** @return resource */
function stateHandle(string $statePath)
{
    $directory = dirname($statePath);
    if (!is_dir($directory) && !mkdir($directory, 0777, true) && !is_dir($directory)) {
        throw new RuntimeException('Unable to create the SDK contract state directory.');
    }
    $handle = fopen($statePath, 'c+');
    if ($handle === false || !flock($handle, LOCK_EX)) {
        throw new RuntimeException('Unable to lock the SDK contract state.');
    }
    return $handle;
}

/** @param resource $handle @return array<string, mixed> */
function readState($handle): array
{
    rewind($handle);
    $contents = stream_get_contents($handle);
    $state = is_string($contents) && $contents !== '' ? json_decode($contents, true) : [];
    return is_array($state) ? $state : [];
}

/** @param resource $handle @param array<string, mixed> $state */
function writeState($handle, array $state): void
{
    rewind($handle);
    ftruncate($handle, 0);
    fwrite($handle, (string) json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    fflush($handle);
    flock($handle, LOCK_UN);
    fclose($handle);
}

function recordResult(string $statePath, string $id, int $status): void
{
    $handle = stateHandle($statePath);
    $state = readState($handle);
    $previous = is_array($state['requests'][$id] ?? null) ? $state['requests'][$id] : [];
    $state['requests'][$id] = [
        'attempted' => true,
        'attempts' => is_int($previous['attempts'] ?? null) ? $previous['attempts'] : 1,
        'status' => $status,
    ];
    writeState($handle, $state);
}

/** @return array<string, string>|null */
function matchTemplate(string $template, string $path): ?array
{
    $template = (string) preg_replace('#^\{\{base_url\}\}/\{\{namespace\}\}/?#i', '', $template);
    $template = explode('?', $template, 2)[0];
    $expected = explode('/', trim($template, '/'));
    $actual = explode('/', trim($path, '/'));
    if (count($expected) !== count($actual)) {
        return null;
    }
    $parameters = [];
    foreach ($expected as $index => $segment) {
        if (preg_match('/^:([A-Za-z][A-Za-z0-9_-]*)$/', $segment, $matches) === 1
            || preg_match('/^\{\{([^}]+)\}\}$/', $segment, $matches) === 1) {
            $parameters[$matches[1]] = rawurldecode($actual[$index]);
        } elseif (rawurldecode($actual[$index]) !== $segment) {
            return null;
        }
    }
    return $parameters;
}

function resolveService(Fleetbase $fleetbase, string $serviceClass): Service
{
    foreach (get_object_vars($fleetbase) as $value) {
        if ($value instanceof $serviceClass && $value instanceof Service) {
            return $value;
        }
    }
    throw new RuntimeException('Unable to resolve SDK service ' . $serviceClass . '.');
}

/** @param array<string, mixed> $request @param array<string, string> $headers @return array{array<string, mixed>, array<string, mixed>} */
function invocation(array $request, string $path, array $headers): array
{
    $parameters = matchTemplate(requiredString($request, 'url'), $path) ?? [];
    $options = requestOptions($headers);
    if ($_GET !== []) {
        $options['query'] = requestData();
    }

    $contentType = strtolower(headerValue($headers, 'Content-Type'));
    $raw = (string) file_get_contents('php://input');
    if (strpos($contentType, 'application/json') !== false && $raw !== '') {
        $body = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        if (is_array($body)) {
            $parameters['body'] = $body;
            if ($body === []) {
                $options['body'] = $raw;
            }
        }
    } elseif (strpos($contentType, 'multipart/form-data') !== false) {
        $parts = [];
        foreach ($_POST as $name => $value) {
            if (is_string($name) && is_scalar($value)) {
                $parts[] = ['name' => $name, 'contents' => (string) $value];
            }
        }
        foreach ($_FILES as $name => $file) {
            if (!is_string($name) || !is_array($file) || !is_string($file['tmp_name'] ?? null)) {
                continue;
            }
            $contents = file_get_contents($file['tmp_name']);
            $part = ['name' => $name, 'contents' => is_string($contents) ? $contents : ''];
            if (is_string($file['name'] ?? null)) {
                $part['filename'] = $file['name'];
            }
            $parts[] = $part;
        }
        $options['multipart'] = $parts;
        foreach (array_keys(is_array($options['headers'] ?? null) ? $options['headers'] : []) as $name) {
            if (is_string($name) && strcasecmp($name, 'Content-Type') === 0) {
                unset($options['headers'][$name]);
            }
        }
    } elseif ($raw !== '') {
        $options['body'] = $raw;
    }
    return [$parameters, $options];
}

/** @param array<string, mixed> $data */
function requiredString(array $data, string $key): string
{
    $value = $data[$key] ?? null;
    if (!is_string($value) || $value === '') {
        throw new RuntimeException('Missing contract field ' . $key . '.');
    }
    return $value;
}

function emitResponse(ResponseInterface $response): void
{
    http_response_code($response->getStatusCode());
    foreach ($response->getHeaders() as $name => $values) {
        if (!in_array(strtolower($name), ['content-length', 'transfer-encoding', 'connection'], true)) {
            header($name . ': ' . implode(', ', $values));
        }
    }
    echo (string) $response->getBody();
}
